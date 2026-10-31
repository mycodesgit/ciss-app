<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use PDF;

use App\Services\Enrollment\ReportCard;

/**
 * Enrollment > Student Report Card: search an enrollment (campus, Student ID, school year, semester), then show the
 * student's Report Card of that term as a PDF.
 *  - index(): the search page
 *  - find():  AJAX, checks the student is enrolled in that term and campus (FindsEnrollment), returns the PDF's URL
 *  - pdf():   the Report Card (src of the PDF modal's iframe); the same check, then App\Services\Enrollment\ReportCard
 * Access: User::canPrintReportCards ('report-card' middleware). Campus: administrators can pick any campus,
 * everyone else only their own. (student-report-card.js)
 */
class StudentReportCardController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    public function index()
    {
        return view('pages.employee.enrollment.report-card.index', $this->searchOptions());
    }

    public function find(Request $request)
    {
        [$student, $term] = $this->enrollmentOf($request);

        return response()->json([
            'name' => trim("{$student->lname}, {$student->fname}"),
            'term' => $term->label,
            'url' => route('employee.enrollment.report-card.pdf', [
                'campus' => $request->input('campus'),
                'stud_id' => $student->stud_id,
                'schlyear' => $term->schlyear,
                'semester' => $term->semester,
            ]),
        ]);
    }

    public function pdf(Request $request)
    {
        [$student, $term, $enrollment] = $this->enrollmentOf($request);
        $campus = $request->input('campus');

        return PDF::loadView('pdf.report-card', ReportCard::of($student, $enrollment, $campus) + [
            'student' => $student,
            'enrollment' => $enrollment,
            'term' => $term,
            'header' => config("ciss.report_card.headers.{$campus}"),
            'registrar' => config("ciss.report_card.registrars.{$campus}"),
            'preparedBy' => $this->user(),
        ])
            ->setPaper('legal', 'portrait')
            ->stream("report-card-{$student->stud_id}.pdf");
    }
}
