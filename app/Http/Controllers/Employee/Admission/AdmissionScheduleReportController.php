<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PDF;

use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\Time;

/**
 * Admission > Reports > Admission Schedules: the applicants booked on one exam schedule (date & time) of a
 * year / campus, with Excel export and a printable list (PDF in a modal's iframe).
 * (public/js/employee/admission/report-schedules.js)
 */
class AdmissionScheduleReportController extends Controller
{
    use ManagesApplicants;

    public function index()
    {
        $options = $this->searchOptions();

        return view('pages.employee.admission.report.schedules', [
            'years' => $options['years'],
            'currentYear' => $options['currentYear'],
            'campuses' => $options['campuses'],
            'userCampus' => $options['userCampus'],
        ]);
    }

    // Exam schedules (ad_time) of the selected year / campus, for the Admission Date & Time select
    public function schedules(Request $request)
    {
        [$year, $campus] = $this->yearAndCampus($request);

        $schedules = Time::whereYear('date', $year)
            ->where('campus', $campus)
            ->orderBy('date')->orderBy('time')
            ->get(['id', 'date', 'time', 'slots']);

        $booked = $this->takenSlots($schedules->pluck('id'));

        return $schedules->map(fn (Time $schedule) => [
            'id' => $schedule->id,
            'label' => $this->scheduleLabel($schedule) . ' (' . ($booked[$schedule->id] ?? 0) . '/' . $schedule->slots . ')',
        ]);
    }

    // DataTables source: the applicants booked on the selected schedule
    public function list(Request $request)
    {
        // Nothing until a schedule is picked (the page's first load)
        if (! $request->filled('schedule')) {
            return response()->json(['data' => []]);
        }

        $applicants = $this->bookedApplicants($this->selectedSchedule($request))
            ->map(fn (Applicant $applicant) => $this->present($applicant));

        return response()->json(['data' => $applicants]);
    }

    // Printable list of the schedule's applicants, the src of the PDF modal's iframe
    public function pdf(Request $request)
    {
        $schedule = $this->selectedSchedule($request);

        return PDF::loadView('pdf.admission-schedule', [
            'schedule' => $schedule,
            'scheduleLabel' => $this->scheduleLabel($schedule),
            'campusName' => config('ciss.campuses')[$schedule->campus] ?? $schedule->campus,
            'applicants' => $this->bookedApplicants($schedule),
        ])
            ->setPaper('legal', 'portrait')
            ->stream('admission-schedule-' . Carbon::parse($schedule->date)->format('Y-m-d') . '.pdf');
    }

    // Year and campus of the search (users who can't see every campus only ever get their own)
    private function yearAndCampus(Request $request): array
    {
        $filters = $request->validate([
            'year' => ['required', 'string'],
            'campus' => ['required', Rule::in(array_keys(config('ciss.campuses')))],
        ]);

        return [$filters['year'], $this->user()->canViewAllCampuses() ? $filters['campus'] : $this->user()->campus];
    }

    // The picked schedule, which must belong to the searched year / campus
    private function selectedSchedule(Request $request): Time
    {
        [$year, $campus] = $this->yearAndCampus($request);
        $request->validate(['schedule' => ['required', 'integer']]);

        return Time::where('campus', $campus)->whereYear('date', $year)->findOrFail($request->input('schedule'));
    }

    // Applicants booked on the schedule (not cancelled), by name
    private function bookedApplicants(Time $schedule)
    {
        return Applicant::with('documents')
            ->where('dateID', $schedule->id)
            ->whereIn('p_status', Applicant::ACTIVE_STATUSES)
            ->orderBy('lname')->orderBy('fname')
            ->get();
    }

    private function scheduleLabel(Time $schedule): string
    {
        return Carbon::parse($schedule->date)->format('M d, Y') . ' ' . Carbon::parse($schedule->time)->format('h:i A');
    }
}
