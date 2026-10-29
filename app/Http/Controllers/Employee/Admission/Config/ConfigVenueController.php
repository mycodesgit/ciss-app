<?php

namespace App\Http\Controllers\Employee\Admission\Config;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ConfiguresAdmission;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\AdmissionDB\Venue;
use App\Models\AdmissionDB\Year;

/**
 * Configure Admission > Venue (ad_venue): exam venues per campus and admission year.
 */
class ConfigVenueController extends Controller
{
    use ConfiguresAdmission;

    public function list()
    {
        $venues = $this->visibleToUser(Venue::query())->orderByDesc('adyear')->orderBy('venue')->get(['id', 'campus', 'adyear', 'venue']);

        return response()->json([
            'data' => $venues->map(fn (Venue $venue) => $venue->only(['id', 'adyear', 'venue']) + $this->campusFields($venue)),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'adyear' => ['required', Rule::exists(Year::class, 'adyear')],
            'venue' => ['required', 'string', 'max:255', $this->uniqueVenue($request->input('adyear'))],
        ], [], ['adyear' => 'admission year']);

        Venue::create($data + ['campus' => $this->user()->campus]);

        return $this->ok('Venue added.');
    }

    // Only the venue name changes (the year stays)
    public function update(Request $request, Venue $venue)
    {
        $this->authorizeOwnCampus($venue);

        $venue->update($request->validate([
            'venue' => ['required', 'string', 'max:255', $this->uniqueVenue($venue->adyear, $venue)],
        ]));

        return $this->ok('Venue updated.');
    }

    public function destroy(Venue $venue)
    {
        $this->authorizeOwnCampus($venue);

        $venue->delete();

        return $this->ok('Venue deleted.');
    }

    // One venue name per campus per year
    private function uniqueVenue($year, ?Venue $venue = null)
    {
        return Rule::unique(Venue::class, 'venue')
            ->where('campus', $this->user()->campus)
            ->where('adyear', $year)
            ->ignore($venue?->id);
    }
}
