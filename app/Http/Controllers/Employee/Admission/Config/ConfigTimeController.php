<?php

namespace App\Http\Controllers\Employee\Admission\Config;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ConfiguresAdmission;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\AdmissionDB\AdmissionDate;
use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\Time;

/**
 * Configure Admission > Time Schedule (ad_time): the exam schedules applicants are booked on (applicant.dateID).
 * Once applicants are booked, a schedule's date and time are kept (only its slots can change) and it can't be deleted.
 */
class ConfigTimeController extends Controller
{
    use ConfiguresAdmission;

    public function list()
    {
        $schedules = $this->visibleToUser(Time::query())->orderByDesc('date')->orderBy('time')->get(['id', 'campus', 'date', 'time', 'slots']);

        // Booked applicants per schedule (one count query)
        $booked = Applicant::whereIn('dateID', $schedules->pluck('id'))
            ->where('p_status', '!=', Applicant::STATUS_CANCELLED)
            ->groupBy('dateID')
            ->selectRaw('dateID, COUNT(*) as total')
            ->pluck('total', 'dateID');

        return response()->json([
            'data' => $schedules->map(fn (Time $schedule) => [
                'id' => $schedule->id,
                'date' => Carbon::parse($schedule->date)->toDateString(),
                'date_label' => Carbon::parse($schedule->date)->format('M d, Y'),
                'time' => Carbon::parse($schedule->time)->format('H:i'),
                'time_label' => Carbon::parse($schedule->time)->format('h:i A'),
                'slots' => (int) $schedule->slots,
                'booked' => (int) ($booked[$schedule->id] ?? 0),
            ] + $this->campusFields($schedule)),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($this->isTaken($data)) {
            return $this->fail('This date and time already has a schedule.');
        }

        Time::create($data + ['campus' => $this->user()->campus]);

        return $this->ok('Time schedule added.');
    }

    public function update(Request $request, Time $time)
    {
        $this->authorizeOwnCampus($time);

        $data = $this->validated($request);
        $booked = $this->bookedCount($time);
        $moved = $data['date'] !== Carbon::parse($time->date)->toDateString() || $data['time'] !== Carbon::parse($time->time)->format('H:i');

        if ($booked > 0 && $moved) {
            return $this->fail("{$booked} applicant(s) are already booked on this schedule. Only the slots can be changed.");
        }
        if ($data['slots'] < $booked) {
            return $this->fail("Slots can't be lower than the {$booked} applicant(s) already booked.");
        }
        if ($moved && $this->isTaken($data, $time)) {
            return $this->fail('This date and time already has a schedule.');
        }

        $time->update($data);

        return $this->ok('Time schedule updated.');
    }

    public function destroy(Time $time)
    {
        $this->authorizeOwnCampus($time);

        if ($booked = $this->bookedCount($time)) {
            return $this->fail("{$booked} applicant(s) are booked on this schedule, so it can't be deleted.");
        }

        $time->delete();

        return $this->ok('Time schedule deleted.');
    }

    private function bookedCount(Time $time): int
    {
        return Applicant::where('dateID', $time->id)->where('p_status', '!=', Applicant::STATUS_CANCELLED)->count();
    }

    // Another schedule of the campus on the same date and time
    private function isTaken(array $data, ?Time $except = null): bool
    {
        return Time::where('campus', $this->user()->campus)
            ->whereDate('date', $data['date'])
            ->whereTime('time', '=', $data['time'])
            ->when($except, fn ($query) => $query->whereKeyNot($except->id))
            ->exists();
    }

    // The date must be one of the campus's admission dates
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'date' => ['required', 'date', Rule::exists(AdmissionDate::class, 'date')->where('campus', $this->user()->campus)],
            'time' => ['required', 'date_format:H:i'],
            'slots' => ['required', 'integer', 'min:1', 'max:10000'],
        ], [
            'date.exists' => 'Choose one of your campus admission dates.',
        ]);

        $data['slots'] = (int) $data['slots'];

        return $data;
    }
}
