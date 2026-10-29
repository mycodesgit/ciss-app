<?php

namespace App\Http\Controllers\Employee\Admission\Config;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ConfiguresAdmission;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\AdmissionDB\AdmissionDate;
use App\Models\AdmissionDB\Time;

/**
 * Configure Admission > Admission Date (ad_admission_date). Time schedules are added on these dates,
 * so a date that already has time schedules can't be changed or deleted.
 */
class ConfigDateController extends Controller
{
    use ConfiguresAdmission;

    public function list()
    {
        $dates = $this->visibleToUser(AdmissionDate::query())->orderByDesc('date')->get(['id', 'campus', 'date']);

        return response()->json([
            'data' => $dates->map(fn (AdmissionDate $date) => [
                'id' => $date->id,
                'date' => Carbon::parse($date->date)->toDateString(),
                'date_label' => Carbon::parse($date->date)->format('F d, Y (l)'),
            ] + $this->campusFields($date)),
        ]);
    }

    // The user's campus dates, for the Date selects of the Time Schedule tab
    public function options()
    {
        return AdmissionDate::where('campus', $this->user()->campus)
            ->orderByDesc('date')
            ->pluck('date')
            ->map(fn ($date) => [
                'value' => Carbon::parse($date)->toDateString(),
                'label' => Carbon::parse($date)->format('M d, Y (D)'),
            ]);
    }

    public function store(Request $request)
    {
        AdmissionDate::create($this->validated($request) + ['campus' => $this->user()->campus]);

        return $this->ok('Admission date added.');
    }

    public function update(Request $request, AdmissionDate $date)
    {
        $this->authorizeOwnCampus($date);

        if ($this->hasSchedules($date)) {
            return $this->fail('This date already has time schedules. Remove them first before changing the date.');
        }

        $date->update($this->validated($request, $date));

        return $this->ok('Admission date updated.');
    }

    public function destroy(AdmissionDate $date)
    {
        $this->authorizeOwnCampus($date);

        if ($this->hasSchedules($date)) {
            return $this->fail('This date already has time schedules. Remove them first before deleting the date.');
        }

        $date->delete();

        return $this->ok('Admission date deleted.');
    }

    private function hasSchedules(AdmissionDate $date): bool
    {
        return Time::where('campus', $date->campus)->whereDate('date', $date->date)->exists();
    }

    // One entry per date per campus
    private function validated(Request $request, ?AdmissionDate $date = null): array
    {
        return $request->validate([
            'date' => ['required', 'date', Rule::unique(AdmissionDate::class, 'date')->where('campus', $this->user()->campus)->ignore($date?->id)],
        ], [], ['date' => 'admission date']);
    }
}
