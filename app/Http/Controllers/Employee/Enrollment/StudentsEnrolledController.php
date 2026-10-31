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

use App\Models\AdmissionDB\Applicant;
use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Enrollment\ClassAttendance;

/**
 * Enrollment > Students Enrolled (the old system's "Students Strands" report): every student enrolled in a campus
 * and term with their senior high strand, birthdate and age, sex, admission type, program and address.
 *  - index(): the page (search: campus, school year, semester, undergraduate / graduate)
 *  - list():  AJAX, the students (DataTable with Excel export)
 * Enrolled = program_en_history.status 2 or 3 (as the old Students Enrolled report). The strand and admission type
 * come from the student's admission application (students.app_id). Graduate students: Student ID ending in -G or -N.
 * Registrar and Administrators only ('registrar' middleware, the old menu's roles). (students-enrolled.js)
 *
 * students.stud_id has no index, so students and applications are read by id in batches of 500 (with more than
 * 1000 values MariaDB turns an IN list into a join that finds nothing) instead of being joined.
 */
class StudentsEnrolledController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    public const TYPES = [1 => 'Undergraduate Students', 2 => 'Graduate Students'];

    private const ENROLLED_STATUSES = [2, 3];
    private const GRADUATE_STUDENT_ID = '/-[GN]$/';
    private const ID_BATCH = 500;

    public function index()
    {
        return view('pages.employee.enrollment.students-enrolled.index', $this->searchOptions() + ['types' => self::TYPES]);
    }

    // Empty until a term is searched (the table loads once when the page opens)
    public function list(Request $request)
    {
        if (! $request->filled('semester')) {
            return response()->json(['data' => []]);
        }

        $data = $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'schlyear' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
        ]);

        $graduate = (int) $data['type'] === 2;
        $enrollments = StudEnrolmentHistory::where('schlyear', $data['schlyear'])
            ->where('semester', $data['semester'])
            ->whereCampusIn('campus', [$data['campus']])
            ->whereIn('status', self::ENROLLED_STATUSES)
            ->orderByDesc('id')
            ->get(['studentID', 'progCod', 'studYear', 'studSec', 'schlyear', 'semester'])
            ->unique('studentID')
            ->filter(fn ($row) => (bool) preg_match(self::GRADUATE_STUDENT_ID, $row->studentID) === $graduate)
            ->values();

        $students = $this->studentsById($enrollments->pluck('studentID'));
        $applications = $this->applications($students->pluck('app_id'));
        $programs = DB::connection('schedule')->table('programs')
            ->whereIn('progCod', $enrollments->pluck('progCod')->filter()->unique()->values()->all() ?: [''])
            ->get(['progCod', 'progName', 'progAcronym'])->keyBy('progCod');

        $term = new ConfigureCurrent(['schlyear' => $data['schlyear'], 'semester' => (int) $data['semester']]);

        return response()->json([
            'data' => $enrollments->map(function ($row) use ($students, $applications, $programs, $term) {
                $student = $students[$row->studentID] ?? null;
                $application = $student ? ($applications[$student->app_id] ?? null) : null;
                $birthday = $student && $student->bday ? rescue(fn () => Carbon::parse($student->bday), null, false) : null;

                return [
                    'studentID' => $row->studentID,
                    'name' => $student ? ClassAttendance::fullName($student) : '',
                    'strand' => $application?->strand,
                    'bday' => $birthday?->format('M d, Y'),
                    'bday_sort' => $birthday?->format('Y-m-d'),
                    'age' => $birthday?->age,
                    'gender' => $student?->gender,
                    'type' => $application ? (Applicant::TYPES[(int) $application->type] ?? $application->type) : null,
                    'progName' => $programs[$row->progCod]->progName ?? null,
                    'progAcronym' => $programs[$row->progCod]->progAcronym ?? $row->progCod,
                    'studYear' => $row->studYear,
                    'studSec' => $row->studSec,
                    'term' => $term->label,
                    'address' => $student?->address,
                    'brgy' => $student?->brgy,
                    'city' => $student?->city,
                    'province' => $student?->province,
                    'region' => $student?->region,
                    'zcode' => $student?->zcode,
                    'lastSchool' => $student ? (trim((string) $student->lstsch_attended) !== '' ? $student->lstsch_attended : $student->suc_lst_attended) : null,
                ];
            })->sortBy('name')->values(),
        ]);
    }

    // ---------- Helpers ----------

    private function studentsById(Collection $studentIds): Collection
    {
        return $studentIds->unique()->chunk(self::ID_BATCH)
            ->flatMap(fn (Collection $batch) => Student::whereIn('stud_id', $batch->values()->all())->get([
                'stud_id', 'app_id', 'lname', 'fname', 'mname', 'ext', 'bday', 'gender', 'address',
                'brgy', 'city', 'province', 'region', 'zcode', 'lstsch_attended', 'suc_lst_attended',
            ]))
            ->keyBy('stud_id');
    }

    // The admission applications of the students (strand, type), by id
    private function applications(Collection $applicationIds): Collection
    {
        return $applicationIds->filter()->unique()->chunk(self::ID_BATCH)
            ->flatMap(fn (Collection $batch) => Applicant::whereIn('id', $batch->values()->all())->get(['id', 'strand', 'type']))
            ->keyBy('id');
    }
}
