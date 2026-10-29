<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use Illuminate\Http\Request;
use PDF;

use App\Models\AdmissionDB\Applicant;

/**
 * Admission > Reports > Accepted Applicants: applicants of a year / campus / strand accepted after the
 * department interview (p_status 5) or already pushed to enrollment (p_status 6), with the accepted course,
 * exportable to Excel and as a printable list (PDF in a modal's iframe). (public/js/employee/admission/report-accepted.js)
 */
class AdmissionAcceptedReportController extends Controller
{
    use ManagesApplicants;

    private const STATUSES = [Applicant::STATUS_ACCEPTED, Applicant::STATUS_ENROLLMENT];

    public function index()
    {
        return view('pages.employee.admission.report.accepted', $this->searchOptions());
    }

    // DataTables source, filtered by the search form
    public function list(Request $request)
    {
        // Nothing until a strand is picked (the page's first load), so one search never loads every strand
        if (! $request->filled('strand')) {
            return response()->json(['data' => []]);
        }

        $applicants = $this->acceptedApplicants($request)
            ->map(fn (Applicant $applicant) => $this->present($applicant) + [
                'accepted_course' => $applicant->deptRating?->course,
                'status_label' => $this->statusLabel($applicant),
                'pushed' => (int) $applicant->p_status === Applicant::STATUS_ENROLLMENT,
            ]);

        return response()->json(['data' => $applicants]);
    }

    // Printable list of the search, the src of the PDF modal's iframe
    public function pdf(Request $request)
    {
        $request->validate(['strand' => ['required', 'string']]);

        $campus = $this->user()->canViewAllCampuses() ? $request->input('campus') : $this->user()->campus;
        $strand = $request->input('strand');
        $applicants = $this->acceptedApplicants($request)->sortBy('display_name')->values();

        return PDF::loadView('pdf.admission-accepted', [
            'applicants' => $applicants,
            'statusLabel' => fn (Applicant $applicant) => $this->statusLabel($applicant),
            'pushedCount' => $applicants->where('p_status', Applicant::STATUS_ENROLLMENT)->count(),
            'year' => $request->input('year'),
            'campusName' => config('ciss.campuses')[$campus] ?? $campus,
            'strandName' => $strand === 'all' ? 'All Strands' : $strand,
        ])
            ->setPaper('legal', 'portrait')
            ->stream('accepted-applicants-' . $request->input('year') . '.pdf');
    }

    // Applicants of the search at "accepted" (5) or "pushed to enrollment" (6)
    private function acceptedApplicants(Request $request)
    {
        return $this->searchApplicants($request, self::STATUSES)
            ->with('deptRating')
            ->get();
    }

    private function statusLabel(Applicant $applicant): string
    {
        return (int) $applicant->p_status === Applicant::STATUS_ENROLLMENT ? 'Pushed to Enrollment' : 'Accepted';
    }
}
