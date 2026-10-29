<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use Illuminate\Http\Request;
use PDF;

use App\Models\AdmissionDB\Applicant;

/**
 * Admission > Reports > Confirmed Applicants: applicants of a year / campus / strand at "confirmed"
 * (p_status 4, ready for the department interview) with their exam remark, exportable to Excel and as a
 * printable list (PDF in a modal's iframe). (public/js/employee/admission/report-confirmed.js)
 */
class AdmissionConfirmedReportController extends Controller
{
    use ManagesApplicants;

    public function index()
    {
        return view('pages.employee.admission.report.confirmed', $this->searchOptions());
    }

    // DataTables source, filtered by the search form
    public function list(Request $request)
    {
        // Nothing until a strand is picked (the page's first load), so one search never loads every strand
        if (! $request->filled('strand')) {
            return response()->json(['data' => []]);
        }

        $applicants = $this->confirmedApplicants($request)
            ->map(fn (Applicant $applicant) => $this->present($applicant) + [
                'remark' => $applicant->result?->displayRemark(),
            ]);

        return response()->json(['data' => $applicants]);
    }

    // Printable list of the search, the src of the PDF modal's iframe
    public function pdf(Request $request)
    {
        $request->validate(['strand' => ['required', 'string']]);

        $campus = $this->user()->canViewAllCampuses() ? $request->input('campus') : $this->user()->campus;
        $strand = $request->input('strand');

        return PDF::loadView('pdf.admission-confirmed', [
            'applicants' => $this->confirmedApplicants($request)->sortBy('display_name')->values(),
            'year' => $request->input('year'),
            'campusName' => config('ciss.campuses')[$campus] ?? $campus,
            'strandName' => $strand === 'all' ? 'All Strands' : $strand,
        ])
            ->setPaper('legal', 'portrait')
            ->stream('confirmed-applicants-' . $request->input('year') . '.pdf');
    }

    // Applicants of the search at "confirmed" (p_status 4)
    private function confirmedApplicants(Request $request)
    {
        return $this->searchApplicants($request, Applicant::STATUS_CONFIRMED)
            ->with('result')
            ->get();
    }
}
