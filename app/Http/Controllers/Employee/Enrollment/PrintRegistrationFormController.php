<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;

/**
 * Enrollment > Print Student RF: search an enrollment (campus, Student ID, school year, semester), then show its
 * Registration Form. The PDF itself is RegistrationFormController's (the same RF as Enroll / Edit Enrollment).
 *  - index(): the search page
 *  - find():  AJAX, checks the student is enrolled in that term and campus, returns the RF's URL
 * Campus: administrators can pick any campus, everyone else only their own (FindsEnrollment). (print-rf.js)
 */
class PrintRegistrationFormController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    public function index()
    {
        return view('pages.employee.enrollment.print-rf.index', $this->searchOptions());
    }

    public function find(Request $request)
    {
        [$student, $term, $enrollment] = $this->enrollmentOf($request);

        return response()->json([
            'name' => trim("{$student->lname}, {$student->fname}"),
            'term' => $term->label,
            'url' => route('employee.enrollment.enroll.registration-form', [
                'student' => $student->stud_id,
                'campus' => $request->input('campus'),
                'schlyear' => $term->schlyear,
                'semester' => $term->semester,
            ]),
        ]);
    }
}
