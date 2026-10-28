<?php

namespace App\Http\Controllers\Employee\Settings;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\SettingDB\Campus;

/**
 * Settings > Campuses Control: allow or block student login per campus (campus.login_enabled).
 * Saved by public/js/employee/settings/control-status.js (same switch contract as Control Status).
 */
class SettingCampusController extends Controller
{
    public function index()
    {
        return view('pages.employee.setting.camp.listcamp', [
            'campuses' => Campus::orderBy('id')->get(['id', 'code', 'name', 'login_enabled']),
        ]);
    }

    public function update(Request $request, Campus $campus)
    {
        $validated = $request->validate([
            'status' => ['required', 'boolean'],
        ]);

        $campus->update(['login_enabled' => (bool) $validated['status']]);

        return response()->json([
            'success' => true,
            'is_on' => (bool) $campus->login_enabled,
            'message' => $campus->name . ($campus->login_enabled ? ': student login is now allowed.' : ': student login is now blocked.'),
        ]);
    }
}
