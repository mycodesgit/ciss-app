<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\EnrollmentDB\Student;
use App\Services\Enrollment\ClassAttendance;
use App\Services\Enrollment\StudentGrades;

/**
 * Enrollment > View Student Grades: search a Student ID, see all of the student's grades per term.
 *  - index(): the page (search: campus, Student ID)
 *  - find():  AJAX, the student and their grades grouped by term (StudentGrades); 422 with the reason when not found
 * Campus: administrators can pick any campus, everyone else only their own (FindsEnrollment).
 * Graduate School staff can only view graduate students (Student ID ending in -G or -N). (student-grades.js)
 */
class StudentGradesController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    private const GRADUATE_STUDENT_ID = '/-[GN]$/';

    public function index()
    {
        return view('pages.employee.enrollment.student-grades.index', $this->searchOptions());
    }

    public function find(Request $request)
    {
        $request->merge(['stud_id' => strtoupper(trim((string) $request->input('stud_id')))]);

        $data = $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'stud_id' => ['required', 'regex:' . $this->studentIdPattern],
        ], ['stud_id.regex' => 'The Student ID Number must look like 2024-0001-K.'], ['stud_id' => 'Student ID Number']);

        $student = Student::where('stud_id', $data['stud_id'])->whereCampusIn('campus', [$data['campus']])->first();

        if (! $student) {
            $this->failEnrollment("Student ID Number {$data['stud_id']} does not exist in this campus.");
        }
        if ($this->isGraduateStaff() && ! preg_match(self::GRADUATE_STUDENT_ID, $student->stud_id)) {
            $this->failEnrollment("Student ID Number {$student->stud_id} is not a Graduate School student.");
        }

        $terms = StudentGrades::of($student, $data['campus']);

        return response()->json([
            'student' => [
                'stud_id' => $student->stud_id,
                'name' => ClassAttendance::fullName($student),
                'campus' => str_replace(' Campus', '', config('ciss.campuses')[$data['campus']] ?? $data['campus']),
            ],
            'terms' => $terms,
            'credits' => (float) $terms->sum('credits'),
        ]);
    }
}
