<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;
use App\Http\Requests\Employee\Admission\UpdateApplicantRequest;
use App\Mail\ApplicantConfirmationMail;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

use App\Models\AdmissionDB\AdReupload;
use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\Time;

/**
 * Admission > List of Applicant: registered applicants (p_status 1) waiting for an exam schedule.
 * The page loads once; the search, table and every action use the JSON endpoints below
 * (public/js/employee/admission/applicants.js).
 *
 * update / updateSchedule / cancel are shared with List of Examinee.
 * Anyone with Admission access can search; only applicants of the user's own campus can be changed.
 */
class AdmissionApplicantController extends Controller
{
    use ManagesApplicants;

    public function index()
    {
        return view('pages.employee.admission.applicant.list', $this->searchOptions() + [
            'reuploadDocuments' => Applicant::REUPLOAD_DOCUMENTS,
        ]);
    }

    // DataTables source, filtered by the search form
    public function list(Request $request)
    {
        $applicants = $this->searchApplicants($request, Applicant::STATUS_REGISTERED)
            ->get()
            ->map(fn (Applicant $applicant) => $this->present($applicant));

        return response()->json(['data' => $applicants]);
    }

    public function update(UpdateApplicantRequest $request, Applicant $applicant)
    {
        $this->authorizeCampus($applicant);

        $applicant->update($request->validated());

        return $this->ok('Applicant personal information updated successfully.');
    }

    public function showReupload(Applicant $applicant)
    {
        return response()->json(['reuploadallow' => $applicant->reupload?->reuploadallow ?? []]);
    }

    public function updateReupload(Request $request, Applicant $applicant)
    {
        $this->authorizeCampus($applicant);

        $validated = $request->validate([
            'reuploadallow' => ['nullable', 'array'],
            'reuploadallow.*' => [Rule::in(array_keys(Applicant::REUPLOAD_DOCUMENTS))],
        ]);

        AdReupload::updateOrCreate(
            ['appid' => $applicant->id],
            ['reuploadallow' => $validated['reuploadallow'] ?? [], 'status' => '2', 'camp' => $applicant->campus]
        );

        return $this->ok('Applicant re-upload access updated successfully.');
    }

    public function updateSchedule(Request $request, Applicant $applicant)
    {
        $this->authorizeCampus($applicant);

        $validated = $request->validate([
            'dateID' => ['required', 'integer'],
            'venue' => ['required', 'string', Rule::in($this->venues()->all())],
        ], [], ['dateID' => 'exam schedule']);

        return DB::transaction(function () use ($applicant, $validated) {
            // Date and time come from the schedule itself, not from the browser
            $schedule = Time::where('campus', $applicant->campus)->lockForUpdate()->find($validated['dateID']);

            if (!$schedule) {
                return $this->fail('The selected exam schedule does not exist for this campus.');
            }

            $isSameSchedule = (int) $applicant->dateID === $schedule->id;
            if (!$isSameSchedule && $this->remainingSlots($schedule) <= 0) {
                return $this->fail('The selected exam schedule is already full. Please choose another one.');
            }

            $applicant->update([
                'dateID' => $schedule->id,
                'd_admission' => $schedule->date,
                'time' => $schedule->time,
                'venue' => $validated['venue'],
            ]);

            return $this->ok('Applicant schedule has been saved.');
        });
    }

    // Moves the applicant to the examinee list and emails them their schedule
    public function pushToExaminees(Applicant $applicant)
    {
        $this->authorizeCampus($applicant);

        if ((int) $applicant->p_status !== Applicant::STATUS_REGISTERED) {
            return $this->fail('This applicant is no longer in the applicant list.');
        }

        if (!$applicant->hasExamSchedule()) {
            return $this->fail('Please assign an exam schedule and venue before pushing to the examinee list.');
        }

        $applicant->update(['p_status' => Applicant::STATUS_SCHEDULED]);

        try {
            Mail::to($applicant->email)->send(new ApplicantConfirmationMail([
                'applicant_name' => $applicant->fname . ' ' . $applicant->lname,
                'admission_id' => $applicant->admission_id,
                'campus' => config('ciss.campuses')[$applicant->campus] ?? $applicant->campus,
                'date' => Carbon::parse($applicant->d_admission)->format('F d, Y'),
                'time' => Carbon::parse($applicant->time)->format('h:i A'),
                'venue' => $applicant->venue,
            ]));
        } catch (\Exception $e) {
            report($e);
            return $this->ok('Applicant pushed to the Examinee List, but the email could not be sent.');
        }

        return $this->ok('Applicant pushed to the Examinee List and notified by email.');
    }

    // "Delete" keeps the record but marks it cancelled (p_status 7), as before
    public function cancel(Applicant $applicant)
    {
        $this->authorizeCampus($applicant);

        $applicant->update(['p_status' => Applicant::STATUS_CANCELLED]);

        return $this->ok('Applicant removed from the list.');
    }
}
