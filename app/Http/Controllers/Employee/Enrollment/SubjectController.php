<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

use App\Models\EnrollmentDB\StudentLevel;
use App\Models\ScheduleDB\College;
use App\Models\ScheduleDB\Department;
use App\Models\ScheduleDB\Subject;
use App\Models\ScheduleDB\SubjectAcademicType;
use App\Models\ScheduleDB\SubjectDeliveryMode;

/**
 * Enrollment > Subjects (Registrar and Administrators: 'registrar' middleware): the subjects master list
 * (schedule.subjects). Add, edit, and Delete = set Inactive (subjstatus 0): subjects are never removed, since
 * offerings and grades point to them; an inactive subject can be made Active again.
 * The subject code is COLLEGE-DEPT-### and is assigned by the server when adding (nextCode() only previews it),
 * so two people adding at once never get the same code. The code, college and department can't be edited.
 * (public/js/employee/enrollment/subjects.js)
 */
class SubjectController extends Controller
{
    private const COLLEGE_IDS = [2, 3, 4, 5, 6, 7, 8]; // the colleges subjects belong to (as before)

    public function index()
    {
        return view('pages.employee.enrollment.subjects.index', [
            'colleges' => College::whereIn('id', self::COLLEGE_IDS)->orderBy('college_name')->get(['college_abbr', 'college_name']),
            'departments' => Department::orderBy('deptName')->get(['deptCod', 'deptName']),
            'levels' => StudentLevel::get(['id', 'studLevel']),
            'deliveryModes' => SubjectDeliveryMode::get(['id', 'delmode']),
            'academicTypes' => SubjectAcademicType::get(['id', 'acadtype_name']),
        ]);
    }

    public function list()
    {
        $columns = ['id', 'sub_code', 'sub_name', 'sub_title', 'subjcollege', 'subjdep', 'sublecredit', 'sublabcredit', 'sub_unit',
            'subjweeks', 'subjconthrs', 'subjlev', 'subdelmod', 'subacadtype', 'subjprereq', 'subjstatus'];
        if ($this->hasCoRequisite()) {
            $columns[] = 'subjcoreq';
        }

        return response()->json([
            'data' => Subject::orderBy('sub_code')->get($columns)->map(fn (Subject $subject) => $subject->toArray() + [
                'active' => (int) $subject->subjstatus !== Subject::INACTIVE,
            ]),
        ]);
    }

    // Preview of the code the next subject of a college + department gets
    public function nextCode(Request $request)
    {
        $data = $request->validate([
            'college' => ['required', Rule::in($this->collegeCodes())],
            'deptCod' => ['required', Rule::exists(Department::class, 'deptCod')],
        ]);

        return response()->json(['code' => $this->nextCodeFor($data['college'], $data['deptCod'])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'college' => ['required', Rule::in($this->collegeCodes())],
            'deptCod' => ['required', Rule::exists(Department::class, 'deptCod')],
        ] + $this->detailRules(), [], $this->attributes());

        $prefix = "{$data['college']}-{$data['deptCod']}";
        $code = $this->withCodeLock($prefix, function () use ($data, $prefix) {
            $code = $this->nextCodeFor($data['college'], $data['deptCod']);

            $subject = Subject::create([
                'sub_code' => $code,
                'subjcostcenter' => $prefix,
                'subjcollege' => $data['college'],
                'subjdep' => $data['deptCod'],
                'subjstatus' => Subject::ACTIVE,
            ] + $this->details($data));

            $this->saveCoRequisite($subject, $data);

            return $code;
        });

        return response()->json(['success' => true, 'message' => "Subject {$code} added."]);
    }

    // Everything but the code, college and department (the code identifies the subject in offerings)
    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate($this->detailRules(), [], $this->attributes());

        $subject->update($this->details($data));
        $this->saveCoRequisite($subject, $data);

        return response()->json(['success' => true, 'message' => "Subject {$subject->sub_code} updated."]);
    }

    // "Delete" = Inactive; Active again restores it. Nothing is removed.
    public function updateStatus(Subject $subject)
    {
        $active = (int) $subject->subjstatus === Subject::INACTIVE;
        $subject->update(['subjstatus' => $active ? Subject::ACTIVE : Subject::INACTIVE]);

        return response()->json([
            'success' => true,
            'message' => "Subject {$subject->sub_code} is now " . ($active ? 'Active' : 'Inactive (deleted from the list of active subjects)') . '.',
        ]);
    }

    // ---------- Helpers ----------

    private function detailRules(): array
    {
        return [
            'sub_name' => ['required', 'string', 'max:50'],
            'sub_title' => ['required', 'string', 'max:255'],
            'sublecredit' => ['required', 'numeric', 'min:0', 'max:30'],
            'sublabcredit' => ['required', 'numeric', 'min:0', 'max:30'],
            'sub_unit' => ['required', 'numeric', 'min:0', 'max:30'],
            'subjweeks' => ['required', 'integer', 'min:1', 'max:52'],
            'subjconthrs' => ['required', 'numeric', 'min:0', 'max:100'],
            'subjlev' => ['required', Rule::exists(StudentLevel::class, 'id')],
            'subdelmod' => ['required', Rule::exists(SubjectDeliveryMode::class, 'id')],
            'subacadtype' => ['required', Rule::exists(SubjectAcademicType::class, 'id')],
            'subjprereq' => ['nullable', 'string', 'max:255'],
            'subjcoreq' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function attributes(): array
    {
        return [
            'deptCod' => 'department', 'sub_name' => 'subject name', 'sub_title' => 'subject description',
            'sublecredit' => 'lec. unit', 'sublabcredit' => 'lab. unit', 'sub_unit' => 'total unit',
            'subjweeks' => 'no. of weeks', 'subjconthrs' => 'hours/weeks', 'subjlev' => 'level',
            'subdelmod' => 'delivery mode', 'subacadtype' => 'academic type',
            'subjprereq' => 'pre-requisite', 'subjcoreq' => 'co-requisite',
        ];
    }

    private function details(array $data): array
    {
        return collect($data)->only([
            'sub_name', 'sub_title', 'sublecredit', 'sublabcredit', 'sub_unit', 'subjweeks', 'subjconthrs',
            'subjlev', 'subdelmod', 'subacadtype', 'subjprereq',
        ])->all();
    }

    // subjcoreq: saved only where the column exists (coas-app never saved it)
    private function saveCoRequisite(Subject $subject, array $data): void
    {
        if ($this->hasCoRequisite()) {
            Subject::whereKey($subject->id)->update(['subjcoreq' => $data['subjcoreq'] ?? null]);
        }
    }

    private function hasCoRequisite(): bool
    {
        return Schema::connection('schedule')->hasColumn('subjects', 'subjcoreq');
    }

    private function collegeCodes(): array
    {
        return College::whereIn('id', self::COLLEGE_IDS)->pluck('college_abbr')->all();
    }

    // COLLEGE-DEPT-### with the highest number used so far + 1
    private function nextCodeFor(string $college, string $dept): string
    {
        $prefix = "{$college}-{$dept}-";
        $last = (int) Subject::where('sub_code', 'like', addcslashes($prefix, '%_\\') . '%')
            ->max(DB::raw("CAST(SUBSTRING_INDEX(sub_code, '-', -1) AS UNSIGNED)"));

        return $prefix . str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
    }

    // One save per code prefix at a time (MySQL named lock), so the number can't be taken twice
    private function withCodeLock(string $prefix, callable $callback)
    {
        $connection = DB::connection('schedule');
        $lock = 'subject_code_' . $prefix;

        abort_unless((int) $connection->scalar('SELECT GET_LOCK(?, 10)', [$lock]) === 1, 503, 'The subject list is busy. Please try again.');

        try {
            return $callback();
        } finally {
            $connection->scalar('SELECT RELEASE_LOCK(?)', [$lock]);
        }
    }
}
