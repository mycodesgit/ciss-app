<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use Illuminate\Http\Request;
use PDF;

use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\ExamineeResult;

/**
 * Admission > Reports > Examination Results: applicants of a year / campus / strand at "with result"
 * (p_status 3) that have an exam result, with the remark (Passed / Qualified / Failed), exportable to Excel and as a printable list
 * (PDF in a modal's iframe). (public/js/employee/admission/report-results.js)
 */
class AdmissionResultReportController extends Controller
{
    use ManagesApplicants;

    public function index()
    {
        return view('pages.employee.admission.report.results', $this->searchOptions());
    }

    // DataTables source, filtered by the search form
    public function list(Request $request)
    {
        // Nothing until a strand is picked (the page's first load), so one search never loads every strand
        if (! $request->filled('strand')) {
            return response()->json(['data' => []]);
        }

        $applicants = $this->examinedApplicants($request)
            ->map(fn (Applicant $applicant) => $this->present($applicant) + [
                'remark' => $applicant->result->displayRemark(),
                'passed' => $applicant->result->percentile !== ExamineeResult::REMARK_FAILED,
            ]);

        return response()->json(['data' => $applicants]);
    }

    // Printable list of the search, the src of the PDF modal's iframe
    public function pdf(Request $request)
    {
        $request->validate(['strand' => ['required', 'string']]);

        $campus = $this->user()->canViewAllCampuses() ? $request->input('campus') : $this->user()->campus;
        $strand = $request->input('strand');
        $applicants = $this->examinedApplicants($request)->sortBy('display_name')->values();

        return PDF::loadView('pdf.admission-results', [
            'applicants' => $applicants,
            'failedCount' => $applicants->where('result.percentile', ExamineeResult::REMARK_FAILED)->count(),
            'year' => $request->input('year'),
            'campusName' => config('ciss.campuses')[$campus] ?? $campus,
            'strandName' => $strand === 'all' ? 'All Strands' : $strand,
        ])
            ->setPaper('legal', 'portrait')
            ->stream('examination-results-' . $request->input('year') . '.pdf');
    }

    // Applicants of the search still at "with result" (p_status 3) that have an exam result
    private function examinedApplicants(Request $request)
    {
        return $this->searchApplicants($request, Applicant::STATUS_WITH_RESULT)
            ->whereHas('result')
            ->with('result')
            ->get();
    }
}
