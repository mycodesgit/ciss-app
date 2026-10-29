<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use Illuminate\Http\Request;

use App\Models\AdmissionDB\Applicant;

/**
 * Admission > Reports > Applicants per School: the applicants of a year / campus / strand (not cancelled)
 * with the school they last attended, sorted by school, exportable to Excel.
 * (public/js/employee/admission/report-schools.js)
 */
class AdmissionSchoolReportController extends Controller
{
    use ManagesApplicants;

    public function index()
    {
        return view('pages.employee.admission.report.schools', $this->searchOptions());
    }

    // DataTables source, filtered by the search form
    public function list(Request $request)
    {
        // Nothing until a strand is picked (the page's first load), so one search never loads every strand
        if (! $request->filled('strand')) {
            return response()->json(['data' => []]);
        }

        $applicants = $this->searchApplicants($request, Applicant::ACTIVE_STATUSES)
            ->get()
            ->map(fn (Applicant $applicant) => $this->present($applicant) + [
                // Senior high school for new students, the college for transferees
                'last_school' => $applicant->lstsch_attended ?: $applicant->suc_lst_attended,
            ]);

        return response()->json(['data' => $applicants]);
    }
}
