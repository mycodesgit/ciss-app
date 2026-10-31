<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PDF;

use App\Models\EnrollmentDB\StudHisLog;
use App\Models\EnrollmentDB\StudSubLog;
use App\Models\EnrollmentDB\Student;
use App\Models\ScholarshipDB\Scholar;
use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Enrollment\ClassAttendance;

/**
 * Enrollment > Enrollment Logs (the old system's "Updated Enroll. Logs"): every time an enrollment of a term was saved.
 * Enroll Student and Edit Enrollment write one log per save: the enrollment (studhislog) and its subjects
 * (studsublog), sharing the save's "encode" code.
 *  - index(): the page (search: campus, school year, semester)
 *  - list():  AJAX, one row per student: last saved, by whom, how many saves (DataTable with Excel export)
 *  - pdf():   every save of one student's enrollment in the term, oldest first, with its subjects (?stud_id=)
 * Registrar and Administrators only ('registrar' middleware, the old menu's roles). (enrollment-logs.js)
 *
 * students.stud_id has no index, so students are read by id in batches of 500 instead of being joined.
 */
class EnrollmentLogsController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    private const ID_BATCH = 500;

    public function index()
    {
        return view('pages.employee.enrollment.enrollment-logs.index', $this->searchOptions());
    }

    // Empty until a term is searched (the table loads once when the page opens)
    public function list(Request $request)
    {
        if (! $request->filled('semester')) {
            return response()->json(['data' => []]);
        }

        $data = $this->search($request);
        $logs = StudHisLog::where('schlyear', $data['schlyear'])
            ->where('semester', $data['semester'])
            ->whereCampusIn('campus', [$data['campus']])
            ->orderByDesc('id')
            ->get(['studentID', 'course', 'postedBy', 'created_at'])
            ->groupBy('studentID');

        $students = $logs->keys()->chunk(self::ID_BATCH)
            ->flatMap(fn (Collection $batch) => Student::whereIn('stud_id', $batch->values()->all())->get(['stud_id', 'lname', 'fname', 'mname', 'ext', 'gender']))
            ->keyBy('stud_id');

        return response()->json([
            'data' => $logs->map(function (Collection $saves, $studentId) use ($students, $data) {
                $latest = $saves->first();
                $student = $students[$studentId] ?? null;

                return [
                    'saved' => $latest->created_at?->format('M d, Y h:i A'),
                    'saved_sort' => $latest->created_at?->format('Y-m-d H:i:s'),
                    'studentID' => $studentId,
                    'name' => $student ? ClassAttendance::fullName($student) : '',
                    'gender' => $student?->gender,
                    'course' => $latest->course,
                    'saves' => $saves->count(),
                    'postedBy' => $latest->postedBy,
                    'pdf_url' => route('employee.enrollment.enrollment-logs.pdf', $data + ['stud_id' => $studentId]),
                ];
            })->values(),
        ]);
    }

    public function pdf(Request $request)
    {
        $request->merge(['stud_id' => strtoupper(trim((string) $request->input('stud_id')))]);
        $data = $this->search($request, ['stud_id' => ['required', 'regex:' . $this->studentIdPattern]]);

        $saves = StudHisLog::where('studentID', $data['stud_id'])
            ->where('schlyear', $data['schlyear'])
            ->where('semester', $data['semester'])
            ->whereCampusIn('campus', [$data['campus']])
            ->orderBy('id')
            ->get();

        if ($saves->isEmpty()) {
            abort(404, "Student ID Number {$data['stud_id']} has no enrollment logs in this term.");
        }

        $subjects = $this->subjectsBySave($data['stud_id'], $saves->pluck('encode'));
        $scholarships = Scholar::whereIn('id', $saves->pluck('studSch')->unique()->values()->all())->pluck('scholar_name', 'id');
        $programs = DB::connection('schedule')->table('programs')
            ->whereIn('progCod', $saves->pluck('progCod')->filter()->unique()->values()->all() ?: [''])
            ->pluck('progName', 'progCod');
        $student = Student::where('stud_id', $data['stud_id'])->first(['stud_id', 'lname', 'fname', 'mname', 'ext']);

        return PDF::loadView('pdf.enrollment-logs', [
            'studentId' => $data['stud_id'],
            'name' => $student ? ClassAttendance::fullName($student) : '',
            'term' => new ConfigureCurrent(['schlyear' => $data['schlyear'], 'semester' => (int) $data['semester']]),
            'campusName' => config('ciss.campuses')[$data['campus']] ?? $data['campus'],
            'saves' => $saves->map(fn ($save) => (object) [
                'savedAt' => $save->created_at?->format('F d, Y h:i A'),
                'postedBy' => $save->postedBy,
                'program' => $programs[$save->progCod] ?? $save->progCod,
                'course' => $save->course,
                'scholarship' => $scholarships[$save->studSch] ?? null,
                'subjects' => $subjects->get($save->encode, collect()),
            ]),
        ])
            ->setPaper('legal', 'portrait')
            ->stream('enrollment-logs-' . $data['stud_id'] . '.pdf');
    }

    // ---------- Helpers ----------

    private function search(Request $request, array $rules = []): array
    {
        return $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'schlyear' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ] + $rules, ['stud_id.regex' => 'The Student ID Number must look like 2024-0001-K.'], ['stud_id' => 'Student ID Number']);
    }

    // The subjects saved with each save of the student, by its encode code
    private function subjectsBySave(string $studentId, Collection $encodes): Collection
    {
        $logs = StudSubLog::where('studID', $studentId)->whereIn('encode', $encodes->filter()->values()->all() ?: [''])->get(['subjID', 'encode']);

        $offered = DB::connection('schedule')->table('sub_offered')
            ->leftJoin('subjects', 'sub_offered.subCode', '=', 'subjects.sub_code')
            ->whereIn('sub_offered.id', $logs->pluck('subjID')->unique()->values()->all() ?: [0])
            ->get(['sub_offered.id', 'sub_offered.subCode', 'sub_offered.subSec', 'sub_offered.subUnit', 'sub_offered.lecFee', 'sub_offered.labFee', 'subjects.sub_name', 'subjects.sub_title'])
            ->keyBy('id');

        return $logs->toBase()
            ->map(function ($log) use ($offered) {
                $subject = $offered[$log->subjID] ?? null;

                return (object) [
                    'encode' => $log->encode,
                    'code' => $subject->subCode ?? null,
                    'name' => $subject->sub_name ?? null,
                    'title' => $subject?->sub_title ?? 'Subject no longer offered',
                    'section' => $subject->subSec ?? null,
                    'units' => $subject->subUnit ?? null,
                    'lecFee' => $subject->lecFee ?? null,
                    'labFee' => $subject->labFee ?? null,
                ];
            })
            ->sortBy('code')
            ->groupBy('encode');
    }
}
