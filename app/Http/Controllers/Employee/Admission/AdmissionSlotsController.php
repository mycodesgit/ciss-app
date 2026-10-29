<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\AdmissionDB\Time;

/**
 * Admission > Availability Slots: exam schedules (ad_time) per date with booked / remaining slots.
 * The page shows only the filters; the cards load by AJAX from list() (public/js/employee/admission/slots.js).
 */
class AdmissionSlotsController extends Controller
{
    use ManagesApplicants;

    public function index()
    {
        $options = $this->searchOptions();

        return view('pages.employee.admission.configure.slotsview', [
            'years' => $options['years'],
            'currentYear' => $options['currentYear'],
            'campuses' => $options['campuses'],
            'userCampus' => $options['userCampus'],
        ]);
    }

    // Slot cards (rendered partial HTML) for the selected year / campus
    public function list(Request $request)
    {
        $filters = $request->validate([
            'year' => ['required', 'digits:4'],
            'campus' => ['required', Rule::in(array_keys(config('ciss.campuses')))],
        ]);

        // Users who can't see every campus only ever get their own campus
        $campus = $this->user()->canViewAllCampuses() ? $filters['campus'] : $this->user()->campus;

        $schedules = Time::whereYear('date', $filters['year'])
            ->where('campus', $campus)
            ->orderBy('date')->orderBy('time')
            ->get(['id', 'date', 'time', 'slots']);

        // Booked = applicants on that schedule that were not cancelled (one count query)
        $taken = $this->takenSlots($schedules->pluck('id'));

        $schedules->each(function (Time $schedule) use ($taken) {
            $schedule->booked = (int) ($taken[$schedule->id] ?? 0);
            $schedule->remaining = max($schedule->slots - $schedule->booked, 0);
        });

        return view('pages.employee.admission.configure.partials.slot-cards', [
            'groupedSlots' => $schedules->groupBy(fn (Time $schedule) => Carbon::parse($schedule->date)->format('Y-m-d')),
            'campusName' => str_replace(' Campus', '', config('ciss.campuses')[$campus]),
        ])->render();
    }
}
