<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;

use Illuminate\Support\Facades\DB;

use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;

/**
 * Enrollment > Extended Students: students of the user's campus enrolled in more than MAX_SEMESTERS terms.
 *  - index(): the page (table, history modal)
 *  - list():  AJAX, the extended students with their number of terms (DataTable)
 * View reuses Enrollment History's history() for the student's enrollments. (extended-students.js)
 */
class ExtendedStudentsController extends Controller
{
    use ChecksEnrollment;

    private const MAX_SEMESTERS = 10;

    public function index()
    {
        return view('pages.employee.enrollment.extended-students.index');
    }

    public function list()
    {
        $campuses = $this->campuses();

        // Number of terms per Student ID in the user's campus, more than MAX_SEMESTERS only
        $terms = StudEnrolmentHistory::whereCampusIn('campus', $campuses)
            ->groupBy('studentID')
            ->havingRaw('COUNT(*) > ?', [self::MAX_SEMESTERS])
            ->select('studentID', DB::raw('COUNT(*) as semesters'));

        $students = Student::joinSub($terms, 'terms', 'students.stud_id', '=', 'terms.studentID')
            ->whereCampusIn('students.campus', $campuses)
            ->orderByDesc('terms.semesters')
            ->orderBy('students.lname')
            ->get(['students.stud_id', 'students.lname', 'students.fname', 'students.mname', 'students.ext', 'students.campus', 'terms.semesters']);

        return response()->json([
            'data' => $students->map(fn (Student $student) => $student->only(['stud_id', 'lname', 'fname', 'mname', 'ext']) + [
                'semesters' => (int) $student->semesters,
                'campus' => str_replace(' Campus', '', config('ciss.campuses')[$student->campus] ?? $student->campus),
                'history_url' => route('employee.enrollment.history.show', $student->stud_id),
            ]),
        ]);
    }
}
