<?php

namespace App\Http\Controllers\Employee\Admission\Config;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ConfiguresAdmission;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\AdmissionDB\Strands;

/**
 * Configure Admission > Strand (ad_strand). The apply form lists every strand, so the table shows all
 * of them; only strands added by the user's campus can be changed.
 */
class ConfigStrandController extends Controller
{
    use ConfiguresAdmission;

    public function list()
    {
        $strands = Strands::orderBy('code')->get(['id', 'campus', 'code', 'strand']);

        return response()->json([
            'data' => $strands->map(fn (Strands $strand) => $strand->only(['id', 'code', 'strand']) + $this->campusFields($strand)),
        ]);
    }

    public function store(Request $request)
    {
        Strands::create($this->validated($request) + ['campus' => $this->user()->campus]);

        return $this->ok('Strand added.');
    }

    public function update(Request $request, Strands $strand)
    {
        $this->authorizeOwnCampus($strand);

        $strand->update($this->validated($request, $strand));

        return $this->ok('Strand updated.');
    }

    public function destroy(Strands $strand)
    {
        $this->authorizeOwnCampus($strand);

        $strand->delete();

        return $this->ok('Strand deleted.');
    }

    // Codes are unique across campuses (the apply form shows all strands together)
    private function validated(Request $request, ?Strands $strand = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique(Strands::class, 'code')->ignore($strand?->id)],
            'strand' => ['required', 'string', 'max:255'],
        ], [], ['code' => 'strand code', 'strand' => 'strand name']);

        return array_map(fn ($value) => mb_strtoupper(trim($value)), $data);
    }
}
