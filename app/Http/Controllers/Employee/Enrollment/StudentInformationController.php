<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Requests\Employee\Enrollment\UpdateStudentInformationRequest;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Models\EnrollmentDB\StudentCvlStatus;
use App\Models\EnrollmentDB\StudentGnderStatus;
use App\Models\SettingDB\Region;

/**
 * Enrollment > Student Information (Administrators, Registrar, Guidance: 'student-info' middleware).
 * Find students of the user's campus, then view / edit a student's information.
 *  - index():  the page (search + results table, student modal)
 *  - list():   AJAX, students matching the search (DataTable); undergraduate or graduate school (Student ID with -G)
 *  - show():   AJAX, one student's information (the modal form)
 *  - update(): save the information. The Student ID, campus and date of admission never change.
 * The address uses the shared address fields (x-employee.address-fields, PortalLocationController lists) and the
 * History tab uses Enrollment History's route. Students are always looked up by Student ID and campus.
 * (public/js/employee/enrollment/student-information.js)
 */
class StudentInformationController extends Controller
{
    use ChecksEnrollment;

    private const MIN_SEARCH_LENGTH = 2;
    private const MAX_RESULTS = 500;
    private const LEVELS = ['undergraduate', 'graduate'];
    private const GRADUATE_CAMPUS = 'MC'; // only the Main Campus has a graduate school

    // The fields of the form (everything the update saves)
    private const FIELDS = [
        'fname', 'mname', 'lname', 'ext', 'gender', 'civil_status', 'bday', 'pbirth', 'contact', 'email', 'religion',
        'region', 'province', 'city', 'brgy', 'hnum', 'zcode', 'address',
        'stud_father', 'stud_mother', 'stud_guardian', 'monthly_income', 'guardian_contact',
        'lstsch_attended', 'lst_sch_attended_year', 'suc_lst_attended',
    ];

    public function index()
    {
        return view('pages.employee.enrollment.student-info.index', [
            'hasGraduateSchool' => in_array(self::GRADUATE_CAMPUS, $this->campuses(), true),
            'genders' => StudentGnderStatus::get(['genderstat_name']),
            'civilStatuses' => StudentCvlStatus::get(['cvlstat_name']),
            'regions' => Region::orderBy('name')->get(['region_id', 'name']),
        ]);
    }

    // Last name or first name containing the search, or a Student ID starting with it; the user's campus only
    public function list(Request $request)
    {
        $data = $request->validate([
            'query' => ['nullable', 'string', 'max:100'],
            'level' => ['nullable', Rule::in(self::LEVELS)],
        ]);
        $query = strtoupper(trim((string) ($data['query'] ?? '')));

        if (mb_strlen($query) < self::MIN_SEARCH_LENGTH) {
            return response()->json(['data' => []]);
        }

        $like = addcslashes($query, '%_\\');
        $students = Student::whereCampusIn('campus', $this->campuses())
            ->where(fn ($where) => $where->where('lname', 'like', "%{$like}%")
                ->orWhere('fname', 'like', "%{$like}%")
                ->orWhere('stud_id', 'like', "{$like}%"))
            ->where('stud_id', ($data['level'] ?? 'undergraduate') === 'graduate' ? 'like' : 'not like', '%-G%')
            ->orderBy('lname')->orderBy('fname')
            ->limit(self::MAX_RESULTS)
            ->get(['stud_id', 'lname', 'fname', 'mname', 'ext', 'gender', 'civil_status', 'campus']);

        $courses = $this->latestCourses($students->pluck('stud_id')->all());

        return response()->json([
            'data' => $students->map(fn (Student $student) => [
                'id' => $student->stud_id,
                'stud_id' => $student->stud_id,
                'name' => $this->fullName($student),
                'gender' => $student->gender,
                'civil_status' => $student->civil_status,
                'campus' => $this->campusName($student->campus),
                'course' => $courses[$student->stud_id] ?? null,
            ]),
            'limited' => $students->count() === self::MAX_RESULTS,
        ]);
    }

    public function show(string $student)
    {
        $student = $this->studentOfCampus($student);

        return response()->json($student->only(self::FIELDS) + [
            'stud_id' => $student->stud_id,
            'name' => $this->fullName($student),
            'bday' => $this->dateValue($student->bday),
            'birthday' => $student->bday ? $this->formatDate($student->bday) : null,
            'date_admission' => $student->date_admission,
            'campus' => $this->campusName($student->campus),
            'course' => $this->latestCourses([$student->stud_id])[$student->stud_id] ?? null,
            'history_url' => route('employee.enrollment.history.show', $student->stud_id),
        ]);
    }

    public function update(UpdateStudentInformationRequest $request, string $student)
    {
        $student = $this->studentOfCampus($student);

        $student->update(collect($request->validated())->only(self::FIELDS)->all());

        return response()->json(['success' => true, 'message' => "Student information of {$student->stud_id} updated."]);
    }

    // ---------- Helpers ----------

    // The student with this Student ID in the user's campus (404 otherwise)
    private function studentOfCampus(string $studentId): Student
    {
        return Student::where('stud_id', strtoupper(trim($studentId)))->whereCampusIn('campus', $this->campuses())->firstOrFail();
    }

    // Program acronym of each student's latest enrollment: [stud_id => progAcronym]
    private function latestCourses(array $studentIds): array
    {
        if (!$studentIds) {
            return [];
        }

        $programs = config('database.connections.schedule.database') . '.programs';
        $latestIds = StudEnrolmentHistory::selectRaw('MAX(id)')->whereIn('studentID', $studentIds)->groupBy('studentID');

        return StudEnrolmentHistory::leftJoin("{$programs} as programs", 'program_en_history.progCod', '=', 'programs.progCod')
            ->whereIn('program_en_history.id', $latestIds)
            ->pluck('programs.progAcronym', 'program_en_history.studentID')
            ->all();
    }

    private function fullName(Student $student): string
    {
        $ext = $student->ext && $student->ext !== 'N/A' ? ' ' . $student->ext : '';

        return trim("{$student->lname}, {$student->fname} {$student->mname}") . $ext;
    }

    private function campusName(?string $campus): string
    {
        return str_replace(' Campus', '', config('ciss.campuses')[$campus] ?? (string) $campus);
    }

    // Y-m-d for the date input (null when the saved value isn't a date)
    private function dateValue(?string $date): ?string
    {
        try {
            return $date ? Carbon::parse($date)->format('Y-m-d') : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatDate(string $date): string
    {
        try {
            return Carbon::parse($date)->format('F j, Y');
        } catch (\Throwable) {
            return $date;
        }
    }
}
