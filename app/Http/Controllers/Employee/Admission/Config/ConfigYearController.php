<?php

namespace App\Http\Controllers\Employee\Admission\Config;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ConfiguresAdmission;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\Year;

/**
 * Configure Admission > Year (admission_year). Years are shared by every campus, so only users who can
 * see all campuses change them. Only one year is "On" (the current admission year) at a time.
 */
class ConfigYearController extends Controller
{
    use ConfiguresAdmission;

    public function list()
    {
        $canManage = $this->user()->canViewAllCampuses();

        return response()->json([
            'data' => Year::orderByDesc('adyear')->get(['id', 'adyear', 'status'])
                ->map(fn (Year $year) => $year->only(['id', 'adyear', 'status']) + ['can_manage' => $canManage]),
        ]);
    }

    // New years start "Off"; switch one on with Edit
    public function store(Request $request)
    {
        $this->authorizeYears();

        $data = $request->validate([
            'adyear' => ['required', 'digits:4', Rule::unique(Year::class, 'adyear')],
        ], [], ['adyear' => 'admission year']);

        Year::create($data + ['status' => 'Off']);

        return $this->ok('Admission year added.');
    }

    public function update(Request $request, Year $year)
    {
        $this->authorizeYears();

        $data = $request->validate([
            'adyear' => ['required', 'digits:4', Rule::unique(Year::class, 'adyear')->ignore($year->id)],
            'status' => ['required', Rule::in(['On', 'Off'])],
        ], [], ['adyear' => 'admission year']);

        if ($data['adyear'] != $year->adyear && $this->hasApplicants($year)) {
            return $this->fail('This year already has applicants, so it can\'t be renamed.');
        }

        DB::transaction(function () use ($year, $data) {
            // Switching this year on switches every other year off
            if ($data['status'] === 'On') {
                Year::whereKeyNot($year->id)->update(['status' => 'Off']);
            }

            $year->update($data);
        });

        return $this->ok('Admission year updated.');
    }

    public function destroy(Year $year)
    {
        $this->authorizeYears();

        if ($year->status === 'On') {
            return $this->fail('This is the current admission year. Switch another year on first.');
        }
        if ($this->hasApplicants($year)) {
            return $this->fail('This year already has applicants, so it can\'t be deleted.');
        }

        $year->delete();

        return $this->ok('Admission year deleted.');
    }

    private function authorizeYears(): void
    {
        abort_unless($this->user()->canViewAllCampuses(), 403, 'Only administrators can manage admission years.');
    }

    private function hasApplicants(Year $year): bool
    {
        return Applicant::where('year', $year->adyear)->exists();
    }
}
