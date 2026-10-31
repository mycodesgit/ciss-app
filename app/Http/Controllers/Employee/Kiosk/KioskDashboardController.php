<?php

namespace App\Http\Controllers\Employee\Kiosk;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\EnrollmentDB\KioskLogs;

/**
 * Kiosk > Dashboard: the logged-in employee's kiosk logs (one per account created or password reset in
 * Kiosk User): today, yesterday, per month of this year (chart) and per month of every year (table).
 * Bulk Generation does not add logs (as in the old system). (kiosk/dashboard.js)
 */
class KioskDashboardController extends Controller
{
    public function index()
    {
        $logs = KioskLogs::where('postedBy', Auth::guard('web')->id());

        $perMonth = (clone $logs)
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderByDesc(DB::raw('YEAR(created_at)'))->orderByDesc(DB::raw('MONTH(created_at)'))
            ->get([DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total')]);

        $thisYear = $perMonth->where('year', now()->year)->pluck('total', 'month');

        return view('pages.employee.kiosk.index', [
            'today' => (clone $logs)->whereDate('created_at', today())->count(),
            'yesterday' => (clone $logs)->whereDate('created_at', today()->subDay())->count(),
            'monthly' => collect(range(1, 12))->map(fn ($month) => (int) ($thisYear[$month] ?? 0))->all(),
            'perMonth' => $perMonth,
        ]);
    }
}
