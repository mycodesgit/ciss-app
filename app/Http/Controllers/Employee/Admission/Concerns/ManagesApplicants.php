<?php

namespace App\Http\Controllers\Employee\Admission\Concerns;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\Strands;
use App\Models\AdmissionDB\Time;
use App\Models\AdmissionDB\Venue;
use App\Models\AdmissionDB\Year;
use App\Models\SettingDB\Region;

/**
 * Shared by the admission list pages (List of Applicant, List of Examinee):
 * search options, campus rules, row data, exam schedules and JSON replies.
 */
trait ManagesApplicants
{
    protected function user()
    {
        return Auth::guard('web')->user();
    }

    // Applicants can only be changed by users of the same campus
    protected function authorizeCampus(Applicant $applicant): void
    {
        abort_unless($applicant->campus === $this->user()->campus, 403, 'You can only manage applicants of your own campus.');
    }

    // Values for the shared search form and the schedule / venue selects
    protected function searchOptions(): array
    {
        $user = $this->user();
        $campuses = config('ciss.campuses');

        return [
            'years' => Year::orderByDesc('adyear')->pluck('adyear'),
            'currentYear' => Year::current(),
            'campuses' => $user->canViewAllCampuses() ? $campuses : [$user->campus => $campuses[$user->campus] ?? $user->campus],
            'userCampus' => $user->campus,
            'strands' => Strands::orderBy('strand')->get(['code', 'strand']),
            'regions' => Region::orderBy('name')->get(['region_id', 'name']),
            'schedules' => $this->examSchedules(),
            'venues' => $this->venues(),
        ];
    }

    // Values for the shared exam result modal (raw score -> stanine / remark preview)
    protected function resultOptions(): array
    {
        return [
            'stanines' => config('ciss.stanines'),
            'stanineRemarks' => config('ciss.stanine_remarks'),
            'passingScore' => config('ciss.passing_scores')[$this->user()->campus] ?? null,
            'maxRawScore' => max(array_keys(config('ciss.stanines'))),
        ];
    }

    protected function validateRawScore(Request $request): int
    {
        return (int) $request->validate([
            'raw_score' => ['required', 'integer', 'min:1', 'max:' . max(array_keys(config('ciss.stanines')))],
        ], [], ['raw_score' => 'raw score'])['raw_score'];
    }

    // Applicants with the given p_status (one value or a list) matching the search form (year / campus / strand)
    protected function searchApplicants(Request $request, int|array $status)
    {
        $user = $this->user();

        $filters = $request->validate([
            'year' => ['required', 'string'],
            'campus' => ['required', Rule::in(array_keys(config('ciss.campuses')))],
            'strand' => ['nullable', 'string'],
        ]);

        // Users who can't see every campus only ever get their own campus
        $campus = $user->canViewAllCampuses() ? $filters['campus'] : $user->campus;

        return Applicant::with('documents')
            ->where('year', $filters['year'])
            ->where('campus', $campus)
            ->whereIn('p_status', (array) $status)
            ->when($filters['strand'] ?? null, fn ($query, $strand) => $query->where('strand', $strand))
            ->orderBy('admission_id');
    }

    // Row data used by the tables and the shared modals
    protected function present(Applicant $applicant): array
    {
        $docs = $applicant->documents;
        $documentUrl = fn ($path) => $path ? asset('storage/' . $path) : null;
        $reportCard = $docs ? ($docs->grade12File ?: $docs->shsFile ?: $docs->transfereeFile ?: $docs->alsFile ?: $docs->lifelongFile) : null;

        return $applicant->only([
            'id', 'admission_id', 'fname', 'mname', 'lname', 'ext', 'gender', 'bday', 'civil_status',
            'contact', 'email', 'address', 'region', 'province', 'city', 'brgy', 'hnum', 'zcode',
            'lstsch_attended', 'strand', 'suc_lst_attended', 'course',
            'preference_1', 'preference_2', 'dateID', 'venue', 'campus', 'status',
        ]) + [
            'display_name' => $applicant->display_name,
            'type_name' => Applicant::TYPES[$applicant->type] ?? '',
            'campus_name' => config('ciss.campuses')[$applicant->campus] ?? $applicant->campus,
            'reupload_status' => Applicant::REUPLOAD_STATUSES[$applicant->status] ?? 'Unknown',
            'registered_at' => $applicant->created_at?->toDateString(),
            'registered_label' => $applicant->created_at?->format('F j, Y'),
            'schedule_at' => $applicant->d_admission ? $applicant->d_admission . ' ' . $applicant->time : null,
            'schedule_label' => $applicant->d_admission
                ? Carbon::parse($applicant->d_admission)->format('M d, Y') . ' ' . Carbon::parse($applicant->time)->format('h:i A')
                : null,
            'school_id_url' => $documentUrl($docs?->studiddoc_image),
            'report_card_url' => $documentUrl($reportCard),
            'proof_url' => $documentUrl($docs?->proofdoc_image),
            'proof_type' => $docs?->typefileproofupload,
            'can_manage' => $applicant->campus === $this->user()->campus,
        ];
    }

    // This year's exam schedules of the user's campus, with remaining slots (one count query)
    protected function examSchedules()
    {
        $schedules = Time::where('campus', $this->user()->campus)
            ->whereYear('date', Year::current())
            ->orderBy('date')->orderBy('time')
            ->get(['id', 'date', 'time', 'slots']);

        $taken = $this->takenSlots($schedules->pluck('id'));

        return $schedules->map(fn (Time $schedule) => [
            'id' => $schedule->id,
            'label' => Carbon::parse($schedule->date)->format('M d, Y') . ' ' . Carbon::parse($schedule->time)->format('h:i A'),
            'remaining' => max($schedule->slots - ($taken[$schedule->id] ?? 0), 0),
        ]);
    }

    protected function remainingSlots(Time $schedule): int
    {
        return max($schedule->slots - ($this->takenSlots([$schedule->id])[$schedule->id] ?? 0), 0);
    }

    protected function takenSlots($scheduleIds)
    {
        return Applicant::whereIn('dateID', $scheduleIds)
            ->where('p_status', '!=', Applicant::STATUS_CANCELLED)
            ->groupBy('dateID')
            ->selectRaw('dateID, COUNT(*) as total')
            ->pluck('total', 'dateID');
    }

    protected function venues()
    {
        return Venue::where('campus', $this->user()->campus)->where('adyear', Year::current())->pluck('venue');
    }

    protected function ok(string $message)
    {
        return response()->json(['success' => true, 'message' => $message]);
    }

    protected function fail(string $message)
    {
        return response()->json(['success' => false, 'message' => $message], 422);
    }
}
