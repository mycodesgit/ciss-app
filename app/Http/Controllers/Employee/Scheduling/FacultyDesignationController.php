<?php

namespace App\Http\Controllers\Employee\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Scheduling\Concerns\ScopesScheduling;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\ScheduleDB\FacDesignation;
use App\Models\ScheduleDB\Faculty;

/**
 * Scheduling > Faculty Designation: the designations (Dean, Program Head, ...) of a campus's faculty in a term,
 * with their deloading units. A faculty member has a designation once per campus and term.
 * list() also returns the campus's faculty for the Add / Edit selects. (scheduling/designations.js)
 */
class FacultyDesignationController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, ScopesScheduling;

    public function index()
    {
        return view('pages.employee.scheduling.designations.index', $this->schedulingOptions() + [
            'colleges' => config('ciss.scheduling.designation_colleges'),
            'designations' => config('ciss.scheduling.designations'),
        ]);
    }

    public function list(Request $request)
    {
        $campus = $this->schedulingCampus($request);
        $term = $this->schedulingTerm($request);
        $faculty = $this->facultyOf($campus);

        $designations = FacDesignation::where('campus', $campus)->where($term)
            ->get(['id', 'fac_id', 'facCollege', 'designation', 'dunit']);
        $colleges = config('ciss.scheduling.designation_colleges');

        return response()->json([
            'data' => $designations->map(fn (FacDesignation $designation) => $designation->only(['id', 'fac_id', 'facCollege', 'designation', 'dunit']) + [
                'faculty' => $faculty[$designation->fac_id] ?? "(faculty #{$designation->fac_id})",
                'college' => $colleges[$designation->facCollege] ?? $designation->facCollege,
            ])->sortBy('faculty')->values(),
            'faculty' => $faculty->map(fn ($name, $id) => ['id' => $id, 'name' => $name])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $campus = $this->schedulingCampus($request);
        $term = $this->schedulingTerm($request);
        $data = $this->validated($request, $campus);

        return $this->exclusively("designations.{$campus}", function () use ($campus, $term, $data) {
            $this->ensureUnique($campus, $term, $data);
            FacDesignation::create($term + $data + ['campus' => $campus]);

            return response()->json(['success' => true, 'message' => "{$data['designation']} designation added."]);
        });
    }

    public function update(Request $request, FacDesignation $designation)
    {
        $this->authorizeCampus($designation->campus);
        $data = $this->validated($request, $designation->campus);
        $term = ['schlyear' => $designation->schlyear, 'semester' => $designation->semester];

        return $this->exclusively("designations.{$designation->campus}", function () use ($designation, $term, $data) {
            $this->ensureUnique($designation->campus, $term, $data, $designation->id);
            $designation->update($data);

            return response()->json(['success' => true, 'message' => "{$data['designation']} designation saved."]);
        });
    }

    public function destroy(FacDesignation $designation)
    {
        $this->authorizeCampus($designation->campus);
        $designation->delete();

        return response()->json(['success' => true, 'message' => "{$designation->designation} designation deleted."]);
    }

    // ---------- Helpers ----------

    private function validated(Request $request, string $campus): array
    {
        return $request->validate([
            'fac_id' => ['required', 'integer', Rule::in($this->facultyOf($campus)->keys()->all())],
            'facCollege' => ['required', Rule::in(array_keys(config('ciss.scheduling.designation_colleges')))],
            'designation' => ['required', Rule::in(array_keys(Arr::collapse(config('ciss.scheduling.designations'))))],
            'dunit' => ['required', 'integer', 'min:0', 'max:99'],
        ], ['fac_id.in' => 'Pick a faculty member of this campus.'], ['fac_id' => 'faculty', 'facCollege' => 'college', 'dunit' => 'unit']);
    }

    private function ensureUnique(string $campus, array $term, array $data, ?int $exceptId = null): void
    {
        $taken = FacDesignation::where('campus', $campus)->where($term)
            ->where('fac_id', $data['fac_id'])->where('designation', $data['designation'])
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages(['fac_id' => "This faculty member is already designated {$data['designation']} in this term."]);
        }
    }

    // [faculty.id => "Lastname, Firstname M."] of a campus (faculty.campus is a list)
    private function facultyOf(string $campus)
    {
        return Faculty::whereCampusIn('campus', [$campus])
            ->orderBy('lname')->orderBy('fname')
            ->get(['id', 'lname', 'fname', 'mname', 'ext'])
            ->mapWithKeys(fn (Faculty $faculty) => [$faculty->id => $faculty->listName()]);
    }
}
