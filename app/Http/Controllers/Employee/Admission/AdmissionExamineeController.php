<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use Illuminate\Http\Request;

use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\ExamineeResult;

/**
 * Admission > List of Examinee: applicants with an exam schedule (p_status 2).
 * Here the exam result (raw score) is encoded and the examinee is pushed to the result list.
 * View Data, Schedule and Delete reuse AdmissionApplicantController's endpoints.
 * (public/js/employee/admission/examinees.js)
 */
class AdmissionExamineeController extends Controller
{
    use ManagesApplicants;

    public function index()
    {
        return view('pages.employee.admission.examinee.list', $this->searchOptions() + $this->resultOptions());
    }

    // DataTables source, filtered by the search form
    public function list(Request $request)
    {
        $examinees = $this->searchApplicants($request, Applicant::STATUS_SCHEDULED)
            ->with('result')
            ->get()
            ->map(fn (Applicant $applicant) => $this->present($applicant) + [
                'raw_score' => $applicant->result?->raw_score,
                'stanine' => ExamineeResult::stanineFor($applicant->result?->raw_score),
                'remark' => $applicant->result?->percentile,
            ]);

        return response()->json(['data' => $examinees]);
    }

    // Encode the raw score; stanine and remark are computed here, not taken from the browser
    public function saveResult(Request $request, Applicant $applicant)
    {
        $this->authorizeCampus($applicant);

        $result = ExamineeResult::record($applicant, $this->validateRawScore($request));

        return $this->ok('Exam result saved: raw score ' . $result->raw_score . ' (' . $result->percentile . ').');
    }

    // Moves the examinee to the examination result list (p_status 3)
    public function pushToResults(Applicant $applicant)
    {
        $this->authorizeCampus($applicant);

        if ((int) $applicant->p_status !== Applicant::STATUS_SCHEDULED) {
            return $this->fail('This applicant is no longer in the examinee list.');
        }

        if (!is_numeric($applicant->result?->raw_score)) {
            return $this->fail('Please assign the exam result (raw score) before pushing to the result list.');
        }

        $applicant->update(['p_status' => Applicant::STATUS_WITH_RESULT]);

        return $this->ok('Examinee pushed to the Examination Result List.');
    }
}
