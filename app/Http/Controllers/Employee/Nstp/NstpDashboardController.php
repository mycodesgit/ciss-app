<?php

namespace App\Http\Controllers\Employee\Nstp;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;

use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Enrollment\NstpEnrollees;

/**
 * NSTP > Dashboard: the students of each NSTP component in the user's campus for the active school year
 * & semester (settings_conf), one card per component (NstpEnrollees::counts).
 */
class NstpDashboardController extends Controller
{
    use ChecksEnrollment;

    public function index()
    {
        $term = ConfigureCurrent::active();
        $counts = $term ? NstpEnrollees::counts($this->campuses(), $term->schlyear, $term->semester) : collect();

        return view('pages.employee.nstp.index', [
            'term' => $term,
            'counts' => $counts,
            'total' => $counts->sum('total'),
        ]);
    }
}
