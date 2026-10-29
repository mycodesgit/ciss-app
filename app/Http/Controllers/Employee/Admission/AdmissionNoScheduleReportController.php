<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use Illuminate\Http\Request;
use PDF;

use App\Models\AdmissionDB\Applicant;

/**
 * Admission > Reports > Applicants No Sched: applicants of a year / campus / strand (not cancelled) that
 * still have no exam schedule or no venue, exportable to Excel and as a printable list (PDF in a modal's
 * iframe). (public/js/employee/admission/report-no-schedule.js)
 */
class AdmissionNoScheduleReportController extends Controller
{
    use ManagesApplicants;

    public function index()
    {
        return view('pages.employee.admission.report.no-schedule', $this->searchOptions());
    }

    // DataTables source, filtered by the search form
    public function list(Request $request)
    {
        // Nothing until a strand is picked (the page's first load), so one search never loads every strand
        if (! $request->filled('strand')) {
            return response()->json(['data' => []]);
        }

        $applicants = $this->unscheduledApplicants($request)
            ->map(fn (Applicant $applicant) => $this->present($applicant) + [
                'missing' => $this->missing($applicant),
            ]);

        return response()->json(['data' => $applicants]);
    }

    // Printable list of the search, the src of the PDF modal's iframe
    public function pdf(Request $request)
    {
        $request->validate(['strand' => ['required', 'string']]);

        $campus = $this->user()->canViewAllCampuses() ? $request->input('campus') : $this->user()->campus;
        $strand = $request->input('strand');

        return PDF::loadView('pdf.admission-noschedule', [
            'applicants' => $this->unscheduledApplicants($request)->sortBy('display_name')->values(),
            'missing' => fn (Applicant $applicant) => $this->missing($applicant),
            'year' => $request->input('year'),
            'campusName' => config('ciss.campuses')[$campus] ?? $campus,
            'strandName' => $strand === 'all' ? 'All Strands' : $strand,
        ])
            ->setPaper('legal', 'portrait')
            ->stream('applicants-no-schedule-' . $request->input('year') . '.pdf');
    }

    // Applicants of the search with no exam schedule or no venue
    private function unscheduledApplicants(Request $request)
    {
        return $this->searchApplicants($request, Applicant::ACTIVE_STATUSES)
            ->where(fn ($query) => $query
                ->whereNull('dateID')->orWhere('dateID', 0)
                ->orWhereNull('venue')->orWhere('venue', ''))
            ->get();
    }

    // What the applicant still lacks: "No schedule", "No venue" or both
    private function missing(Applicant $applicant): string
    {
        return collect([
            empty($applicant->dateID) ? 'No schedule' : null,
            blank($applicant->venue) ? 'No venue' : null,
        ])->filter()->implode(', ');
    }
}
