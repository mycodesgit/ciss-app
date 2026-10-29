<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PDF;

use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\DeptRating;
use App\Models\AdmissionDB\Programs;

/**
 * Admission > Confirmed Applicants: examinees confirmed for the department interview (p_status 4).
 * The interview result is recorded here and completed interviews are pushed to the Accepted list.
 * View Data reuses AdmissionApplicantController::update. (public/js/employee/admission/confirmed.js)
 *
 * Guidance Officer / Staff (roles 1, 2) can view but not record interviews or push.
 */
class AdmissionConfirmController extends Controller
{
    use ManagesApplicants;

    public function index()
    {
        return view('pages.employee.admission.confirm.list', $this->searchOptions() + [
            'programs' => $this->campusPrograms(),
            'interviewRemarks' => DeptRating::REMARKS,
            'canRecordInterviews' => $this->user()->canRecordInterviews(),
        ]);
    }

    // DataTables source, filtered by the search form
    public function list(Request $request)
    {
        $applicants = $this->searchApplicants($request, Applicant::STATUS_CONFIRMED)
            ->with('deptRating')
            ->get()
            ->map(function (Applicant $applicant) {
                $rating = $applicant->deptRating;

                return $this->present($applicant) + [
                    'rating' => $rating?->rating,
                    'interview_remarks' => $rating?->remarks,
                    'interview_remarks_name' => DeptRating::REMARKS[$rating?->remarks] ?? DeptRating::REMARKS[2],
                    'interview_course' => $rating?->course,
                    'interview_reason' => $rating?->reason,
                    'interview_completed' => (bool) $rating?->isCompleted(),
                ];
            });

        return response()->json(['data' => $applicants]);
    }

    // Assign Interview Result
    public function saveInterview(Request $request, Applicant $applicant)
    {
        $this->authorizeInterview($applicant);

        $validated = $request->validate([
            'rating' => ['required', 'numeric', 'min:0', 'max:100'],
            'remarks' => ['required', Rule::in(array_keys(DeptRating::REMARKS))],
            'course' => ['required', Rule::in($this->campusPrograms()->pluck('code')->all())],
            'reason' => ['nullable', 'string', 'max:1000'],
        ], [], ['reason' => 'comments']);

        $user = $this->user();

        DeptRating::updateOrCreate(
            ['app_id' => $applicant->id],
            $validated + [
                'camp' => $applicant->campus,
                'admission_id' => $applicant->admission_id,
                'interviewer' => $user->fname . ' ' . $user->lname,
                'interviewerid' => $user->id,
            ]
        );

        return $this->ok('Applicant interview result has been saved.');
    }

    // Moves an applicant with a completed interview to the Accepted list (p_status 5)
    public function pushToAccepted(Applicant $applicant)
    {
        $this->authorizeInterview($applicant);

        if ((int) $applicant->p_status !== Applicant::STATUS_CONFIRMED) {
            return $this->fail('This applicant is no longer in the confirmed list.');
        }

        if (!$applicant->deptRating?->isCompleted()) {
            return $this->fail('Please complete the interview result before pushing to the Accepted Applicants list.');
        }

        $applicant->update(['p_status' => Applicant::STATUS_ACCEPTED]);
        $applicant->deptRating->update(['deptcol' => $this->user()->dept]);

        return $this->ok('Applicant has been pushed to the Accepted Applicants list.');
    }

    // ---------- Helpers ----------

    private function authorizeInterview(Applicant $applicant): void
    {
        $this->authorizeCampus($applicant);
        abort_unless($this->user()->canRecordInterviews(), 403, 'Guidance users can view interview results but not change them.');
    }

    // Programs offered by the user's campus (the course an applicant is interviewed for)
    private function campusPrograms()
    {
        return Programs::whereRaw("FIND_IN_SET(?, REPLACE(campus, ' ', '')) > 0", [$this->user()->campus])
            ->orderBy('program')
            ->get(['code', 'program']);
    }
}
