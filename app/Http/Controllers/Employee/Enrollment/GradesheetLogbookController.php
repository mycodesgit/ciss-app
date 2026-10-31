<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PDF;

use App\Models\EnrollmentDB\GradesheetLogbook;
use App\Models\ScheduleDB\College;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Enrollment > Gradesheet Logbook: when the faculty of a college submitted their gradesheets.
 *  - index(): the page (search: campus, school year, semester, college)
 *  - list():  AJAX, the submitted gradesheets of the college (gradesheetlogbook, newest first; DataTable with Excel export)
 *  - pdf():   the logbook to sign: every class handled by the college's faculty in the term (Graduate School subjects
 *             left out), with the date its gradesheet was submitted (blank when not yet) and a Signature column
 * The logbook is written when a faculty submits a gradesheet in the faculty portal (one row per subject and term).
 * Campus: administrators can pick any campus, everyone else only their own (FindsEnrollment).
 * Same users as Student Record ('student-record' middleware, the old menu's roles). (gradesheet-logbook.js)
 */
class GradesheetLogbookController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    private const COLLEGE_IDS = [2, 3, 4, 5, 6, 7, 8]; // as the old system

    public function index()
    {
        return view('pages.employee.enrollment.gradesheet-logbook.index', $this->searchOptions() + [
            'colleges' => College::whereIn('id', self::COLLEGE_IDS)->orderBy('college_name')->pluck('college_name', 'college_abbr'),
        ]);
    }

    // Empty until a term is searched (the table loads once when the page opens)
    public function list(Request $request)
    {
        if (! $request->filled('semester')) {
            return response()->json(['data' => []]);
        }

        $data = $this->search($request);
        $logs = GradesheetLogbook::where('schlyear', $data['schlyear'])
            ->where('semester', $data['semester'])
            ->whereCampusIn('campus', [$data['campus']])
            ->where('collegeabbr', $data['college'])
            ->orderByDesc('timebeingsubmitted')
            ->get(['facultyid', 'subjectid', 'timebeingsubmitted']);

        $schedule = DB::connection('schedule');
        $faculty = $schedule->table('faculty')->whereIn('id', $logs->pluck('facultyid')->unique()->values()->all() ?: [0])
            ->get(['id', 'lname', 'fname'])->keyBy('id');
        $subjects = $schedule->table('sub_offered')
            ->leftJoin('subjects', 'sub_offered.subCode', '=', 'subjects.sub_code')
            ->whereIn('sub_offered.id', $logs->pluck('subjectid')->unique()->values()->all() ?: [0])
            ->get(['sub_offered.id', 'sub_offered.subSec', 'subjects.sub_name', 'subjects.sub_title', 'subjects.subjcollege'])
            ->keyBy('id');

        $term = $this->term($data);

        return response()->json([
            'data' => $logs->map(function ($log) use ($faculty, $subjects, $term) {
                $teacher = $faculty[$log->facultyid] ?? null;
                $subject = $subjects[$log->subjectid] ?? null;
                $submitted = $this->submittedAt($log->timebeingsubmitted);

                return [
                    'term' => $term->label,
                    'faculty' => $teacher ? trim($teacher->lname . ', ' . $teacher->fname, ', ') : '',
                    'subject' => $subject?->sub_name,
                    'title' => $subject?->sub_title,
                    'section' => $subject?->subSec,
                    'college' => $subject?->subjcollege,
                    'submitted' => $submitted?->format('M d, Y h:i A'),
                    'submitted_sort' => $submitted?->format('Y-m-d H:i:s'),
                ];
            })->values(),
        ]);
    }

    public function pdf(Request $request)
    {
        $data = $this->search($request);
        $term = $this->term($data);

        // Every class of the term handled by the college's faculty (a class with two schedules is listed once)
        $classes = DB::connection('schedule')->table('sub_offered')
            ->join('scheduleclass', 'sub_offered.id', '=', 'scheduleclass.subject_id')
            ->join('faculty', 'scheduleclass.faculty_id', '=', 'faculty.id')
            ->leftJoin('subjects', 'sub_offered.subCode', '=', 'subjects.sub_code')
            ->where('sub_offered.schlyear', $data['schlyear'])
            ->where('sub_offered.semester', $data['semester'])
            ->whereCampusIn('sub_offered.campus', [$data['campus']])
            ->where('faculty.faccollege', $data['college'])
            ->where('sub_offered.subCode', 'NOT LIKE', '%-GSS-%')
            ->orderBy('faculty.lname')->orderBy('faculty.fname')->orderBy('subjects.sub_name')
            ->get(['sub_offered.id', 'sub_offered.subSec', 'subjects.sub_name', 'subjects.sub_title', 'faculty.lname', 'faculty.fname'])
            ->unique('id')
            ->values();

        $submitted = $this->submissions($data, $classes->pluck('id'));

        return PDF::loadView('pdf.gradesheet-logbook', [
            'term' => $term,
            'college' => College::where('college_abbr', $data['college'])->value('college_name') ?? $data['college'],
            'campusName' => config('ciss.campuses')[$data['campus']] ?? $data['campus'],
            'classes' => $classes->map(fn ($class) => (object) [
                'faculty' => trim($class->lname . ', ' . $class->fname, ', '),
                'subject' => $class->sub_name,
                'title' => $class->sub_title,
                'section' => $class->subSec,
                'submitted' => $submitted[$class->id] ?? null,
            ]),
        ])
            ->setPaper('legal', 'landscape')
            ->stream('gradesheet-logbook-' . $data['college'] . '.pdf');
    }

    // ---------- Helpers ----------

    private function search(Request $request): array
    {
        return $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'schlyear' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
            'college' => ['required', Rule::in(College::whereIn('id', self::COLLEGE_IDS)->pluck('college_abbr')->all())],
        ]);
    }

    private function term(array $data): ConfigureCurrent
    {
        return new ConfigureCurrent(['schlyear' => $data['schlyear'], 'semester' => (int) $data['semester']]);
    }

    // When each class's gradesheet was submitted ("Apr 19, 2026"), by class id
    private function submissions(array $data, Collection $classIds): Collection
    {
        return $classIds->chunk(500)
            ->flatMap(fn (Collection $batch) => GradesheetLogbook::where('schlyear', $data['schlyear'])
                ->where('semester', $data['semester'])
                ->whereIn('subjectid', $batch->values()->all())
                ->get(['subjectid', 'timebeingsubmitted']))
            ->toBase()
            ->mapWithKeys(fn ($log) => [$log->subjectid => $this->submittedAt($log->timebeingsubmitted)?->format('M d, Y')]);
    }

    // timebeingsubmitted is a string column
    private function submittedAt(?string $value): ?Carbon
    {
        return $value ? rescue(fn () => Carbon::parse($value), null, false) : null;
    }
}
