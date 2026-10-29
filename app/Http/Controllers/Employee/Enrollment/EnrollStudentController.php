<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\ShowsQueuePanel;

use Illuminate\Http\Request;

/**
 * Enrollment > Enroll Student, search page.
 *  - index():       Student ID + school year + semester, queue panel and pre-enrolled list
 *  - findStudent(): AJAX check of the search; returns the enrollment form's URL when the student can enroll
 * The form is EnrollmentFormController; the queue panel's actions are EnrollmentQueueController.
 * (public/js/employee/enrollment/enroll.js)
 */
class EnrollStudentController extends Controller
{
    use ChecksEnrollment, ShowsQueuePanel;

    public function index()
    {
        $terms = $this->openTerms();

        return view('pages.employee.enrollment.studenroll.enroll', $this->queuePanelData() + [
            'schoolYears' => $terms->pluck('schlyear')->unique()->values(),
            'semesters' => $terms->pluck('semester')->unique()->sort()->values(),
        ]);
    }

    public function findStudent(Request $request)
    {
        [$student, $term] = $this->enrollableStudent($request);

        return response()->json([
            'url' => route('employee.enrollment.enroll.create', [
                'student' => $student->stud_id,
                'schlyear' => $term->schlyear,
                'semester' => $term->semester,
            ]),
        ]);
    }
}
