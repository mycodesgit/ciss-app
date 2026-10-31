<?php

namespace App\Http\Controllers\Employee\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Scheduling\Concerns\ScopesScheduling;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\ScheduleDB\Addressee;
use App\Models\ScheduleDB\College;
use App\Models\ScheduleDB\Department;
use App\Models\ScheduleDB\Faculty;
use App\Support\CampusList;

/**
 * Scheduling > Faculty List: the faculty of a campus (faculty.campus is a list such as "MC,CA"; campactive is where
 * they teach now).
 *  - store(): a newly hired faculty member, in the selected campus
 *  - search() + assign(): an existing faculty member of another campus is added to the selected campus, which
 *    becomes their active campus
 *  - update(): only faculty of a campus the user may manage
 * Faculty are never deleted (schedules and grades point to them). (scheduling/faculty.js)
 */
class FacultyController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, ScopesScheduling;

    private const MAX_SEARCH_RESULTS = 15;

    public function index()
    {
        return view('pages.employee.scheduling.faculty.index', $this->schedulingOptions() + [
            'colleges' => $this->colleges(),
            'prefixes' => $this->prefixes(),
            'suffixes' => config('ciss.scheduling.suffixes'),
            'ranks' => config('ciss.scheduling.ranks'),
            'majors' => config('ciss.scheduling.majors'),
            'majorDepartment' => config('ciss.scheduling.major_department'),
        ]);
    }

    public function list(Request $request)
    {
        $campus = $this->schedulingCampus($request);
        $prefixes = Addressee::pluck('adrDesc', 'id');
        $campusNames = config('ciss.campuses');

        $faculty = Faculty::whereCampusIn('campus', [$campus])->orderBy('lname')->orderBy('fname')
            ->get(['id', 'lname', 'fname', 'mname', 'ext', 'prefix', 'suffix', 'email', 'faccollege', 'facdept', 'deptmajor', 'rank', 'campus', 'campactive']);

        return response()->json([
            'data' => $faculty->map(fn (Faculty $member) => $member->only(['id', 'lname', 'fname', 'mname', 'ext', 'prefix', 'suffix', 'email', 'faccollege', 'facdept', 'deptmajor', 'rank']) + [
                'name' => $member->listName(),
                'prefix_label' => $prefixes[$member->prefix] ?? '',
                'department' => trim($member->facdept . ($member->deptmajor ? " - {$member->deptmajor}" : '')),
                'campuses' => collect(CampusList::split($member->campus))->map(fn ($code) => str_replace(' Campus', '', $campusNames[$code] ?? $code))->implode(', '),
                'active_campus' => str_replace(' Campus', '', $campusNames[$member->campactive] ?? (string) $member->campactive),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $campus = $this->schedulingCampus($request);
        $data = $this->validated($request);

        return $this->exclusively("faculty.{$campus}", function () use ($campus, $data) {
            $this->ensureUniqueName([$campus], $data);

            Faculty::create($data + ['campus' => $campus, 'campactive' => $campus, 'remember_token' => Str::random(60)]);

            return response()->json(['success' => true, 'message' => "{$data['lname']}, {$data['fname']} added."]);
        });
    }

    public function update(Request $request, Faculty $faculty)
    {
        $this->authorizeCampus($faculty->campus);
        $data = $this->validated($request) + $request->validate(['rank' => ['nullable', Rule::in(config('ciss.scheduling.ranks'))]]);
        $campuses = CampusList::split($faculty->campus);

        return $this->exclusively('faculty.' . implode('.', $campuses), function () use ($faculty, $data, $campuses) {
            $this->ensureUniqueName($campuses, $data, $faculty->id);
            $faculty->update($data);

            return response()->json(['success' => true, 'message' => "{$faculty->lname}, {$faculty->fname} saved."]);
        });
    }

    // Existing faculty not yet in the campus whose last or first name contains ?q (at least 2 characters)
    public function search(Request $request)
    {
        $campus = $this->schedulingCampus($request);
        $search = trim((string) $request->validate(['q' => ['nullable', 'string', 'max:50']])['q']);

        if (mb_strlen($search) < 2) {
            return response()->json(['results' => []]);
        }

        $like = '%' . addcslashes($search, '%_\\') . '%';
        $campusNames = config('ciss.campuses');

        $faculty = Faculty::where(fn ($where) => $where->where('lname', 'like', $like)->orWhere('fname', 'like', $like))
            ->whereRaw('FIND_IN_SET(?, REPLACE(IFNULL(campus, \'\'), \' \', \'\')) = 0', [$campus])
            ->orderBy('lname')->orderBy('fname')
            ->limit(self::MAX_SEARCH_RESULTS)
            ->get(['id', 'lname', 'fname', 'mname', 'ext', 'campus']);

        return response()->json([
            'results' => $faculty->map(fn (Faculty $member) => [
                'id' => $member->id,
                'text' => $member->listName() . ' (' . collect(CampusList::split($member->campus))->map(fn ($code) => $campusNames[$code] ?? $code)->implode(', ') . ')',
            ]),
        ]);
    }

    // Adds the selected campus to an existing faculty member and makes it their active campus
    public function assign(Request $request, Faculty $faculty)
    {
        $campus = $this->schedulingCampus($request);

        $campuses = CampusList::split($faculty->campus);
        if (! in_array($campus, $campuses, true)) {
            $campuses[] = $campus;
        }

        $faculty->update(['campus' => implode(',', $campuses), 'campactive' => $campus]);

        $name = config("ciss.campuses.{$campus}", $campus);

        return response()->json(['success' => true, 'message' => "{$faculty->lname}, {$faculty->fname} now teaches in {$name}."]);
    }

    // ---------- Helpers ----------

    private function validated(Request $request): array
    {
        foreach (['lname', 'fname', 'mname', 'ext'] as $field) {
            $request->merge([$field => preg_replace('/\s+/', ' ', trim((string) $request->input($field))) ?: null]);
        }
        $majorDepartment = config('ciss.scheduling.major_department');

        $data = $request->validate([
            'faccollege' => ['required', Rule::in($this->colleges()->keys()->all())],
            'facdept' => ['required', 'string', Rule::exists(Department::class, 'deptCod')->where('collegeCod', (string) $request->input('faccollege'))],
            'deptmajor' => ['nullable', "required_if:facdept,{$majorDepartment}", Rule::in(config('ciss.scheduling.majors'))],
            'lname' => ['required', 'string', 'max:100'],
            'fname' => ['required', 'string', 'max:100'],
            'mname' => ['nullable', 'string', 'max:100'],
            'ext' => ['nullable', 'string', 'max:10'],
            'prefix' => ['required', 'integer', Rule::in($this->prefixes()->keys()->all())],
            'suffix' => ['nullable', Rule::in(config('ciss.scheduling.suffixes'))],
            'email' => ['nullable', 'email', 'max:255'],
        ], [
            'facdept.exists' => 'Pick a department of the selected college.',
            'deptmajor.required_if' => 'Pick the major of a ' . $majorDepartment . ' faculty member.',
        ], [
            'faccollege' => 'college', 'facdept' => 'department', 'deptmajor' => 'major',
            'lname' => 'last name', 'fname' => 'first name', 'mname' => 'middle name',
        ]);

        // A major only goes with its department
        if ($data['facdept'] !== $majorDepartment) {
            $data['deptmajor'] = null;
        }

        return $data;
    }

    private function ensureUniqueName(array $campuses, array $data, ?int $exceptId = null): void
    {
        $taken = Faculty::whereCampusIn('campus', $campuses)
            ->where('lname', $data['lname'])->where('fname', $data['fname'])
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages(['lname' => "{$data['lname']}, {$data['fname']} is already in the faculty list of this campus."]);
        }
    }

    // [college_abbr => college_name] of the academic colleges
    private function colleges()
    {
        return College::whereIn('id', config('ciss.scheduling.college_ids'))->orderBy('college_name')->pluck('college_name', 'college_abbr');
    }

    private function prefixes()
    {
        return Addressee::whereNotIn('id', config('ciss.scheduling.hidden_prefix_ids'))->orderBy('id')->pluck('adrDesc', 'id');
    }
}
