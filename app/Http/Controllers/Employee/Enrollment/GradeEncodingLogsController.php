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

use App\Models\EnrollmentDB\EncodedGrade;
use App\Models\EnrollmentDB\Student;
use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Enrollment\ClassAttendance;

/**
 * Enrollment > Grade Encoding Logs (the old system's "Encoded Grades. Logs"): every save of a final grade or a
 * completion in a term (studgrades_logs, written by Gradesheet, Correction of Grades and the faculty portal).
 *  - index(): the page (search: campus, school year, semester and a Student ID, student name or subject)
 *  - list():  AJAX, the matching saves, newest first (DataTable with Excel export)
 * A term of a campus can have tens of thousands of saves, so a student or a subject must be searched (as Kiosk User).
 * The logs have no term: the term is the one of the subject offered (sub_offered).
 * Registrar and Administrators only ('registrar' middleware, the old menu's roles). (grade-encoding-logs.js)
 *
 * students.stud_id has no index and MariaDB finds nothing in an IN list of more than 1000 values, so ids are
 * looked up in batches of 500 instead of being joined.
 */
class GradeEncodingLogsController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    private const ID_BATCH = 500;
    private const MAX_STUDENTS = 200;
    private const MAX_ROWS = 5000;

    public function index()
    {
        return view('pages.employee.enrollment.grade-encoding-logs.index', $this->searchOptions());
    }

    // Empty until searched (the table loads once when the page opens)
    public function list(Request $request)
    {
        if (! $request->filled('semester')) {
            return response()->json(['data' => []]);
        }

        $data = $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'schlyear' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
            'q' => ['required', 'string', 'min:2', 'max:60'],
        ], [], ['q' => 'Student ID, name or subject']);

        $search = trim($data['q']);
        $subjects = $this->termSubjects($data);

        // Saves of the matching students in the term's subjects, and saves in the matching subjects
        $studentIds = $this->matchingStudentIds($search, $data['campus']);
        $matchedSubjectIds = $subjects->filter(fn ($subject) => stripos($subject->sub_name . ' ' . $subject->subCode, $search) !== false)->keys();

        $logs = $this->logsOf('studsID', $studentIds)->filter(fn ($log) => isset($subjects[$log->subjctsID]))
            ->merge($this->logsOf('subjctsID', $matchedSubjectIds))
            ->unique('id')
            ->sortByDesc('id')
            ->take(self::MAX_ROWS)
            ->values();

        $students = $logs->pluck('studsID')->unique()->chunk(self::ID_BATCH)
            ->flatMap(fn (Collection $batch) => Student::whereIn('stud_id', $batch->values()->all())->get(['stud_id', 'lname', 'fname', 'mname', 'ext']))
            ->keyBy('stud_id');

        return response()->json([
            'data' => $logs->map(function ($log) use ($subjects, $students) {
                $subject = $subjects[$log->subjctsID] ?? null;
                $student = $students[$log->studsID] ?? null;
                $completion = (string) $log->datecgrade !== '';
                $savedAt = $this->savedAt($completion ? $log->datecgrade : $log->datefgrade) ?? $log->created_at;

                return [
                    'saved' => $savedAt?->format('M d, Y h:i A'),
                    'saved_sort' => $savedAt?->format('Y-m-d H:i:s'),
                    'studentID' => $log->studsID,
                    'name' => $student ? ClassAttendance::fullName($student) : '',
                    'subject' => $subject ? trim($subject->sub_name . ' - ' . $subject->subSec, ' -') : '',
                    'kind' => $completion ? 'Completion' : 'Final Grade',
                    'fgrade' => $log->fgrade,
                    'cgrade' => $log->cgrade,
                    'encodedBy' => $log->encodedBy,
                ];
            }),
            // Too many matches: only some students or the newest saves are listed
            'limited' => $logs->count() >= self::MAX_ROWS || $studentIds->count() >= self::MAX_STUDENTS,
        ]);
    }

    // ---------- Helpers ----------

    // The subjects offered in the campus and term, by id, with their name. subjects.sub_code has no index,
    // so the names are read by code instead of joined (a join takes about 2 seconds)
    private function termSubjects(array $data): Collection
    {
        $schedule = DB::connection('schedule');
        $offered = $schedule->table('sub_offered')
            ->where('schlyear', $data['schlyear'])
            ->where('semester', $data['semester'])
            ->whereCampusIn('campus', [$data['campus']])
            ->get(['id', 'subCode', 'subSec']);

        $names = $offered->pluck('subCode')->filter()->unique()->chunk(self::ID_BATCH)
            ->mapWithKeys(fn (Collection $batch) => $schedule->table('subjects')->whereIn('sub_code', $batch->values()->all())->pluck('sub_name', 'sub_code'));

        return $offered->each(fn ($subject) => $subject->sub_name = $names[$subject->subCode] ?? $subject->subCode)->keyBy('id');
    }

    // Students of the campus whose Student ID starts with, or whose last / first name contains, the search
    private function matchingStudentIds(string $search, string $campus): Collection
    {
        $like = '%' . addcslashes($search, '%_\\') . '%';

        return Student::whereCampusIn('campus', [$campus])
            ->where(fn ($query) => $query->where('stud_id', 'like', addcslashes($search, '%_\\') . '%')
                ->orWhere('lname', 'like', $like)
                ->orWhere('fname', 'like', $like)
                ->orWhereRaw("CONCAT(lname, ', ', fname) LIKE ?", [$like]))
            ->limit(self::MAX_STUDENTS)
            ->pluck('stud_id');
    }

    private function logsOf(string $column, Collection $values): Collection
    {
        return $values->unique()->chunk(self::ID_BATCH)
            ->flatMap(fn (Collection $batch) => EncodedGrade::whereIn($column, $batch->values()->all())
                ->get(['id', 'studsID', 'subjctsID', 'datefgrade', 'datecgrade', 'fgrade', 'cgrade', 'encodedBy', 'created_at']))
            ->toBase();
    }

    // datefgrade / datecgrade are string columns
    private function savedAt(?string $value): ?Carbon
    {
        return $value ? rescue(fn () => Carbon::parse($value), null, false) : null;
    }
}
