<?php

namespace App\Http\Controllers\Employee\Kiosk;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PDF;

use App\Models\EnrollmentDB\KioskUser;
use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Enrollment\ClassAttendance;
use App\Services\Kiosk\KioskAccounts;

/**
 * Kiosk > Bulk Generation: kiosk passwords for a whole class (program + year + section) of a term.
 *  - index():    the page (search: campus, school year, semester)
 *  - list():     AJAX, one row per class with its number of students (male / female) and of kiosk accounts
 *  - students(): AJAX, the students of one class and whether each has a kiosk account (modal)
 *  - generate(): POST, sets new passwords (students without an account, or all) and returns the password
 *                list as a PDF, the only time the passwords can be read (only their hashes are saved)
 * Enrolled = program_en_history.status 2 or 3; Graduate School staff see graduate school programs only.
 * Campus: administrators can pick any campus, everyone else only their own (FindsEnrollment). (kiosk/bulk.js)
 *
 * students.stud_id has no index, so students and accounts are read by Student ID in batches of 500 (with more than
 * 1000 values MariaDB turns an IN list into a join that finds nothing) instead of being joined.
 */
class KioskBulkController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    private const ENROLLED_STATUSES = [2, 3];
    private const GRADUATE_PROGRAMS = '%-GSS-%';
    private const ID_BATCH = 500;

    public function index()
    {
        return view('pages.employee.kiosk.bulk.index', $this->searchOptions());
    }

    // Empty until a term is searched (the table loads once when the page opens)
    public function list(Request $request)
    {
        if (! $request->filled('semester')) {
            return response()->json(['data' => []]);
        }

        $data = $this->validateTerm($request);
        $enrollments = $this->enrolled($data)->get(['studentID', 'progCod', 'studYear', 'studSec'])->unique('studentID');
        $students = $this->studentsById($enrollments->pluck('studentID'), ['stud_id', 'gender']);
        $accounts = $this->accountIds($enrollments->pluck('studentID'));
        $programs = $this->programs($enrollments->pluck('progCod')->unique());

        $rows = $enrollments
            ->groupBy(fn ($row) => "{$row->progCod}|{$row->studYear}|{$row->studSec}")
            ->map(function (Collection $class) use ($data, $students, $accounts, $programs) {
                $first = $class->first();
                $gender = fn ($row) => strtoupper((string) ($students[$row->studentID]->gender ?? ''));

                return [
                    'id' => "{$first->progCod}|{$first->studYear}|{$first->studSec}",
                    'progCod' => $first->progCod,
                    'progName' => $programs[$first->progCod]->progName ?? null,
                    'progAcronym' => $programs[$first->progCod]->progAcronym ?? null,
                    'section' => "{$first->studYear}-{$first->studSec}",
                    'total' => $class->count(),
                    'male' => $class->filter(fn ($row) => $gender($row) === 'MALE')->count(),
                    'female' => $class->filter(fn ($row) => $gender($row) === 'FEMALE')->count(),
                    'accounts' => $class->filter(fn ($row) => isset($accounts[$row->studentID]))->count(),
                    'class' => $data + ['progCod' => $first->progCod, 'studYear' => $first->studYear, 'studSec' => $first->studSec],
                ];
            })
            ->sortBy(fn ($row) => [$row['progAcronym'] ?? $row['progCod'], $row['section']])
            ->values();

        return response()->json(['data' => $rows]);
    }

    public function students(Request $request)
    {
        $students = $this->classStudents($this->validateClass($request));
        $accounts = $this->accountIds($students->pluck('stud_id'));

        return response()->json([
            'data' => $students->map(fn ($student) => [
                'studentID' => $student->stud_id,
                'name' => ClassAttendance::fullName($student),
                'gender' => $student->gender,
                'hasAccount' => isset($accounts[$student->stud_id]),
            ])->values(),
        ]);
    }

    // mode "missing": only students without a kiosk account; "all": also resets the existing passwords
    public function generate(Request $request)
    {
        $data = $this->validateClass($request) + $request->validate(['mode' => ['required', Rule::in(['missing', 'all'])]]);
        $students = $this->classStudents($data);
        $accounts = $this->accountIds($students->pluck('stud_id'));
        $targets = $data['mode'] === 'all' ? $students : $students->reject(fn ($student) => isset($accounts[$student->stud_id]));

        abort_if($targets->isEmpty(), 422, 'Every student of this class already has a kiosk account.');

        $user = $this->user();
        DB::connection('enrollment')->transaction(function () use ($targets, $user) {
            $targets->each(function ($student) use ($user) {
                $student->kiosk_password = KioskAccounts::generatePassword();
                KioskAccounts::setPassword($student->stud_id, $student->kiosk_password, $user, false);
            });
        });

        $term = new ConfigureCurrent(['schlyear' => $data['schlyear'], 'semester' => (int) $data['semester']]);
        $programName = $this->programs(collect([$data['progCod']]))->first()->progName ?? $data['progCod'];
        $targets->each(fn ($student) => $student->full_name = ClassAttendance::fullName($student));

        return PDF::loadView('pdf.kiosk-passwords', [
            'title' => trim("{$programName} {$data['studYear']}-{$data['studSec']}"),
            'term' => $term,
            'campusName' => config('ciss.campuses')[$data['campus']] ?? $data['campus'],
            'groups' => [
                'Male Students' => $targets->filter(fn ($student) => strtoupper((string) $student->gender) === 'MALE'),
                'Female Students' => $targets->filter(fn ($student) => strtoupper((string) $student->gender) === 'FEMALE'),
                'Other / Unknown Gender' => $targets->reject(fn ($student) => in_array(strtoupper((string) $student->gender), ['MALE', 'FEMALE'], true)),
            ],
            'total' => $targets->count(),
        ])
            ->setPaper('legal', 'portrait')
            ->stream('kiosk-passwords.pdf')
            ->header('Cache-Control', 'no-store'); // the passwords must not be kept by the browser
    }

    // ---------- Helpers ----------

    private function validateTerm(Request $request): array
    {
        return $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'schlyear' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ]);
    }

    private function validateClass(Request $request): array
    {
        return $this->validateTerm($request) + $request->validate([
            'progCod' => ['required', 'string', 'max:50'],
            'studYear' => ['required', 'string', 'max:10'],
            'studSec' => ['required', 'string', 'max:10'],
        ]);
    }

    // Enrollments of the term in the campus (Graduate School staff: graduate programs only)
    private function enrolled(array $data)
    {
        return StudEnrolmentHistory::where('schlyear', $data['schlyear'])
            ->where('semester', $data['semester'])
            ->whereCampusIn('campus', [$data['campus']])
            ->whereIn('status', self::ENROLLED_STATUSES)
            ->when($this->isGraduateStaff(), fn ($query) => $query->where('progCod', 'like', self::GRADUATE_PROGRAMS));
    }

    // The students of one class with a student record, by last name
    private function classStudents(array $data): Collection
    {
        $studentIds = $this->enrolled($data)
            ->where('progCod', $data['progCod'])
            ->where('studYear', $data['studYear'])
            ->where('studSec', $data['studSec'])
            ->pluck('studentID')
            ->unique();

        return $this->studentsById($studentIds, ['stud_id', 'lname', 'fname', 'mname', 'ext', 'gender'])
            ->sortBy(fn ($student) => [$student->lname, $student->fname])
            ->values();
    }

    private function studentsById(Collection $studentIds, array $columns): Collection
    {
        return $studentIds->unique()->chunk(self::ID_BATCH)
            ->flatMap(fn (Collection $batch) => Student::whereIn('stud_id', $batch->values()->all())->get($columns))
            ->keyBy('stud_id');
    }

    // [studid => true] of the students that have a kiosk account
    private function accountIds(Collection $studentIds): array
    {
        return $studentIds->unique()->chunk(self::ID_BATCH)
            ->flatMap(fn (Collection $batch) => KioskUser::whereIn('studid', $batch->values()->all())->pluck('studid'))
            ->flip()->map(fn () => true)->all();
    }

    private function programs(Collection $programCodes): Collection
    {
        return DB::connection('schedule')->table('programs')
            ->whereIn('progCod', $programCodes->filter()->values()->all() ?: [''])
            ->get(['progCod', 'progName', 'progAcronym'])
            ->keyBy('progCod');
    }
}
