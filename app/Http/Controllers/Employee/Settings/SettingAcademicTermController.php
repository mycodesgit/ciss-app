<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\Settings\AcademicTermRequest;

use App\Models\SettingDB\ConfigureCurrent;

/**
 * Settings > A.Y. & Semester (settings_conf). The page loads once; the table and the
 * add/edit modals use the JSON endpoints below (public/js/employee/settings/academic-terms.js).
 */
class SettingAcademicTermController extends Controller
{
    public function index()
    {
        return view('pages.employee.setting.aysem.semlist', [
            'semesters' => ConfigureCurrent::SEMESTERS,
            'statuses' => ConfigureCurrent::STATUSES,
        ]);
    }

    // DataTables source
    public function list()
    {
        $terms = ConfigureCurrent::orderByDesc('schlyear')
            ->orderByDesc('semester')
            ->get(['id', 'schlyear', 'semester', 'set_status'])
            ->map(fn (ConfigureCurrent $term) => $term->only(['id', 'schlyear', 'semester', 'set_status']) + [
                'semester_name' => $term->semester_name,
                'status_name' => $term->status_name,
            ]);

        return response()->json(['data' => $terms]);
    }

    public function store(AcademicTermRequest $request)
    {
        ConfigureCurrent::create($request->validated());

        return $this->ok('A.Y. & Semester added successfully.');
    }

    public function update(AcademicTermRequest $request, ConfigureCurrent $academicTerm)
    {
        $academicTerm->update($request->validated());

        return $this->ok('A.Y. & Semester updated successfully.');
    }

    private function ok(string $message)
    {
        return response()->json(['success' => true, 'message' => $message]);
    }
}
