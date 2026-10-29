<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use Illuminate\Http\Request;

use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\ExamineeResult;

/**
 * Admission > Examination Results: examinees with an encoded result (p_status 3).
 * The result can be corrected (with "Second Qualifier" for failed examinees) and passing
 * examinees are pushed to the Confirm list. View Data reuses AdmissionApplicantController::update.
 * (public/js/employee/admission/results.js)
 */
class AdmissionResultController extends Controller
{
    use ManagesApplicants;

    public function index()
    {
        return view('pages.employee.admission.result.list', $this->searchOptions() + $this->resultOptions());
    }

    // DataTables source, filtered by the search form
    public function list(Request $request)
    {
        $results = $this->searchApplicants($request, Applicant::STATUS_WITH_RESULT)
            ->with('result')
            ->get()
            ->map(fn (Applicant $applicant) => $this->present($applicant) + [
                'raw_score' => $applicant->result?->raw_score,
                'remark' => $applicant->result?->percentile,
                'qualifier' => $applicant->result?->qualifier,
                'can_proceed' => (bool) $applicant->result?->canProceed(),
            ]);

        return response()->json(['data' => $results]);
    }

    // Update Test Result: raw score (remark computed here) + optional Second Qualifier for failed results
    public function updateResult(Request $request, Applicant $applicant)
    {
        $this->authorizeCampus($applicant);

        $rawScore = $this->validateRawScore($request);
        $result = ExamineeResult::record($applicant, $rawScore, $request->boolean('second_qualifier'));

        $message = 'Test result updated: raw score ' . $result->raw_score . ' (' . $result->percentile . ')';

        return $this->ok($message . ($result->qualifier ? ', marked as Second Qualifier.' : '.'));
    }

    // Moves a passing (or second-qualifier) examinee to the Confirm list (p_status 4)
    public function pushToConfirmation(Applicant $applicant)
    {
        $this->authorizeCampus($applicant);

        if ((int) $applicant->p_status !== Applicant::STATUS_WITH_RESULT) {
            return $this->fail('This examinee is no longer in the examination result list.');
        }

        if (!$applicant->result?->canProceed()) {
            return $this->fail('Only examinees who passed (or are marked as Second Qualifier) can be pushed to the Confirm list.');
        }

        $applicant->update(['p_status' => Applicant::STATUS_CONFIRMED]);

        return $this->ok('Examinee pushed to the Confirm List.');
    }
}
