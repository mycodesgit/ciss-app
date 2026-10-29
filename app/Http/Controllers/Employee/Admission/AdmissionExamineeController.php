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
        return view('pages.employee.admission.examinee.list', $this->searchOptions() + [
            'stanines' => config('ciss.stanines'),
            'stanineRemarks' => config('ciss.stanine_remarks'),
            'maxRawScore' => max(array_keys(config('ciss.stanines'))),
        ]);
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

        $validated = $request->validate([
            'raw_score' => ['required', 'integer', 'min:1', 'max:' . max(array_keys(config('ciss.stanines')))],
        ], [], ['raw_score' => 'raw score']);

        $remark = ExamineeResult::remarkFor(ExamineeResult::stanineFor($validated['raw_score']));

        ExamineeResult::updateOrCreate(
            ['app_id' => $applicant->id],
            [
                'camp' => $applicant->campus,
                'admission_id' => $applicant->admission_id,
                'raw_score' => $validated['raw_score'],
                'percentile' => $remark,
                'rawscoredate' => now(),
            ]
        );

        return $this->ok('Exam result saved: raw score ' . $validated['raw_score'] . ' (' . $remark . ').');
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
