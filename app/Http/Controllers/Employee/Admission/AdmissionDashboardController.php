<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\Year;

/**
 * Admission > Dashboard. The page shows the user's campus summary; the
 * all-campus chart loads by AJAX from chart() (public/js/employee/admission/dashboard.js).
 */
class AdmissionDashboardController extends Controller
{
    public function index()
    {
        $year = Year::current();
        $campus = Auth::guard('web')->user()->campus;

        // One grouped query instead of one count per status
        $statusCounts = Applicant::join('ad_applicant_dept_rating', 'ad_applicant_admission.id', '=', 'ad_applicant_dept_rating.app_id')
            ->where('ad_applicant_admission.year', $year)
            ->where('ad_applicant_admission.campus', $campus)
            ->groupBy('ad_applicant_admission.p_status')
            ->selectRaw('ad_applicant_admission.p_status, COUNT(*) as total')
            ->pluck('total', 'p_status');

        return view('pages.employee.admission.index', [
            'currentYear' => $year,
            'registeredCount' => $statusCounts[Applicant::STATUS_REGISTERED] ?? 0,
            'examineeCount' => $statusCounts[Applicant::STATUS_SCHEDULED] ?? 0,
            'withResultCount' => $statusCounts[Applicant::STATUS_WITH_RESULT] ?? 0,
            'confirmedCount' => $statusCounts[Applicant::STATUS_CONFIRMED] ?? 0,
        ]);
    }

    // Registered (no schedule) vs scheduled applicants per campus, this admission year
    public function chart()
    {
        $counts = Applicant::where('year', Year::current())
            ->whereIn('p_status', [Applicant::STATUS_REGISTERED, Applicant::STATUS_SCHEDULED])
            ->groupBy('campus', 'p_status')
            ->selectRaw('campus, p_status, COUNT(*) as total')
            ->get();

        $campuses = collect(config('ciss.campuses'));
        $countFor = fn ($code, $status) => (int) ($counts->first(fn ($row) => $row->campus === $code && (int) $row->p_status === $status)?->total ?? 0);

        return response()->json([
            'labels' => $campuses->map(fn ($name) => str_replace(' Campus', '', $name))->values(),
            'registered' => $campuses->keys()->map(fn ($code) => $countFor($code, Applicant::STATUS_REGISTERED)),
            'scheduled' => $campuses->keys()->map(fn ($code) => $countFor($code, Applicant::STATUS_SCHEDULED)),
        ]);
    }
}
