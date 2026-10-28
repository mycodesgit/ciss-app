<?php

namespace App\Http\Controllers\Employee\Settings;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\SettingDB\GradePass;

/**
 * Settings > Grades Password (gradepass_conf): one grade authorization password per campus.
 * The page loads once; the table and modals use the JSON endpoints below
 * (public/js/employee/settings/grade-passwords.js).
 */
class SettingGradePassController extends Controller
{
    private const DATE_FORMAT = 'F d, Y h:i A';

    public function index()
    {
        return view('pages.employee.setting.gradepass.gpass', [
            'campuses' => config('ciss.campuses'),
        ]);
    }

    // DataTables source
    public function list()
    {
        $campuses = config('ciss.campuses');

        $passwords = GradePass::orderBy('campus')
            ->get(['id', 'gradeauthpass', 'campus', 'created_at', 'updated_at'])
            ->map(fn (GradePass $pass) => [
                'id' => $pass->id,
                'gradeauthpass' => $pass->gradeauthpass,
                'campus' => $pass->campus,
                'campus_name' => $campuses[$pass->campus] ?? $pass->campus,
                'created_at' => $pass->created_at?->timestamp,
                'created_at_label' => $pass->created_at?->format(self::DATE_FORMAT),
                'updated_at' => $pass->updated_at?->timestamp,
                'updated_at_label' => $pass->updated_at?->format(self::DATE_FORMAT),
            ]);

        return response()->json(['data' => $passwords]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'campus' => [
                'required',
                Rule::in(array_keys(config('ciss.campuses'))),
                Rule::unique('settings.gradepass_conf', 'campus'),
            ],
            'gradeauthpass' => ['required', 'string', 'min:4', 'max:191'],
        ], [
            'campus.unique' => 'This campus already has a grades password. Edit it instead.',
        ], [
            'gradeauthpass' => 'grades password',
        ]);

        GradePass::create($validated);

        return $this->ok('Grades password added successfully.');
    }

    public function update(Request $request, GradePass $gradePass)
    {
        $validated = $request->validate([
            'gradeauthpass' => ['required', 'string', 'min:4', 'max:191'],
        ], [], [
            'gradeauthpass' => 'grades password',
        ]);

        $gradePass->update($validated);

        return $this->ok('Grades password updated successfully.');
    }

    private function ok(string $message)
    {
        return response()->json(['success' => true, 'message' => $message]);
    }
}
