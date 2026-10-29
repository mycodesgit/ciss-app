<?php

namespace App\Http\Controllers\Employee\Settings;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use App\Models\AdmissionDB\User;
use App\Models\SettingDB\AdmissionMode;
use App\Models\SettingDB\EnrollmentMode;
use App\Models\SettingDB\QueueMode;
use App\Models\EvaluationDB\QCEsetting;

/**
 * Accounts / Settings > Dashboard. The user counts are cached (cleared by the User model whenever a user
 * changes); the control switches are read live so a change in Control Status shows right away.
 */
class SettingController extends Controller
{
    private const CACHE_SECONDS = 1000;

    public function index()
    {
        $user = Auth::guard('web')->user();

        // The system overview is for administrators only
        if (!$user->isAdministrator()) {
            return view('pages.employee.setting.index', ['overview' => null]);
        }

        $counts = $this->userCounts();

        return view('pages.employee.setting.index', [
            'overview' => [
                'userCounts' => $counts['total'],
                'userActiveCounts' => $counts['active'],
                'userInactiveCounts' => $counts['total'] - $counts['active'],
                'userAddedTodayCounts' => $counts['today'],
                'admissionStatus' => AdmissionMode::value('statusadmission'),
                'enrollmentStatus' => EnrollmentMode::where('campus', $user->campus)->value('statusenroll')
                    ?? EnrollmentMode::value('statusenroll'),
                'queueStatus' => QueueMode::value('statusqueue'),
                'facultyEvalStatus' => QCEsetting::value('statuseval'),
            ],
        ]);
    }

    // Total / active / added today users (one query, cached)
    private function userCounts(): array
    {
        return Cache::remember(User::dashboardCountsCacheKey(), self::CACHE_SECONDS, function () {
            $row = User::query()
                ->selectRaw('COUNT(*) as total, SUM(statuser = ?) as active, SUM(DATE(created_at) = ?) as today', [User::ACTIVE_STATUS, today()->toDateString()])
                ->first();

            return ['total' => (int) $row->total, 'active' => (int) $row->active, 'today' => (int) $row->today];
        });
    }
}
