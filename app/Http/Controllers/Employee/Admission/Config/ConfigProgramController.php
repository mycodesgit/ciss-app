<?php

namespace App\Http\Controllers\Employee\Admission\Config;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ConfiguresAdmission;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\AdmissionDB\Programs;

/**
 * Configure Admission > Programs (ad_programs). A program's campus can list several campuses ("MC, VC"):
 * the table shows every program offered by the user's campus, but only programs of the user's campus
 * alone can be changed.
 */
class ConfigProgramController extends Controller
{
    use ConfiguresAdmission;

    public function list()
    {
        $programs = Programs::query()
            ->when(! $this->user()->canViewAllCampuses(), fn ($query) => $query->whereRaw("FIND_IN_SET(?, REPLACE(campus, ' ', '')) > 0", [$this->user()->campus]))
            ->orderBy('code')
            ->get(['id', 'campus', 'code', 'program']);

        return response()->json([
            'data' => $programs->map(fn (Programs $program) => $program->only(['id', 'code', 'program']) + $this->campusFields($program)),
        ]);
    }

    public function store(Request $request)
    {
        Programs::create($this->validated($request) + ['campus' => $this->user()->campus]);

        return $this->ok('Program added.');
    }

    public function update(Request $request, Programs $program)
    {
        $this->authorizeOwnCampus($program);

        $program->update($this->validated($request, $program));

        return $this->ok('Program updated.');
    }

    public function destroy(Programs $program)
    {
        $this->authorizeOwnCampus($program);

        $program->delete();

        return $this->ok('Program deleted.');
    }

    // Codes are unique within a campus
    private function validated(Request $request, ?Programs $program = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique(Programs::class, 'code')->where('campus', $this->user()->campus)->ignore($program?->id)],
            'program' => ['required', 'string', 'max:255'],
        ], [], ['code' => 'program code', 'program' => 'program name']);

        return array_map(fn ($value) => mb_strtoupper(trim($value)), $data);
    }
}
