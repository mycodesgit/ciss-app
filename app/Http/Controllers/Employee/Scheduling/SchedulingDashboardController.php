<?php

namespace App\Http\Controllers\Employee\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;

use Illuminate\Support\Facades\Cache;

use App\Models\ScheduleDB\College;
use App\Models\ScheduleDB\EnPrograms;
use App\Models\ScheduleDB\Room;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Scheduling > Dashboard: counts of colleges, programs (undergraduate / graduate) and the user's campus rooms
 * (cached 5 minutes, like the old system), the active term, a month calendar, and the latest schedules plotted
 * (Livewire component scheduling.recent-schedules, which refreshes itself).
 */
class SchedulingDashboardController extends Controller
{
    use ChecksEnrollment;

    private const CACHE_SECONDS = 300;

    public function index()
    {
        $graduate = '%' . config('ciss.scheduling.graduate_program_code') . '%';
        $campus = $this->campuses()[0] ?? '';

        $counts = Cache::remember("scheduling_dashboard_counts_{$campus}", self::CACHE_SECONDS, fn () => [
            'colleges' => College::whereIn('id', config('ciss.scheduling.college_ids'))->count(),
            'undergraduate' => EnPrograms::where('progCod', 'not like', $graduate)->count(),
            'graduate' => EnPrograms::where('progCod', 'like', $graduate)->count(),
            'rooms' => Room::where('campus', $campus)->count(),
        ]);

        return view('pages.employee.scheduling.index', [
            'counts' => $counts,
            'term' => ConfigureCurrent::active(),
            'month' => now()->startOfMonth(),
        ]);
    }
}
