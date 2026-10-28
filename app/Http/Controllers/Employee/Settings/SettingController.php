<?php

namespace App\Http\Controllers\Employee\Settings;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

use App\Models\AdmissionDB\User;
use App\Models\SettingDB\AdmissionMode;
use App\Models\SettingDB\EnrollmentMode;
use App\Models\SettingDB\QueueMode;

class SettingController extends Controller
{
    public function index()
    {
        $user = Auth::guard('web')->user();

        // The system overview is for administrators only
        if (!$user->isAdministrator()) {
            return view('pages.employee.setting.index', ['overview' => null]);
        }

        return view('pages.employee.setting.index', [
            'overview' => [
                'userCounts' => User::count(),
                'userActiveCounts' => User::active()->count(),
                'userInactiveCounts' => User::inactive()->count(),
                'userAddedTodayCounts' => User::whereDate('created_at', today())->count(),
                'admissionStatus' => AdmissionMode::value('statusadmission'),
                'enrollmentStatus' => EnrollmentMode::where('campus', $user->campus)->value('statusenroll')
                    ?? EnrollmentMode::value('statusenroll'),
                'queueStatus' => QueueMode::value('statusqueue'),
            ],
        ]);
    }
}
