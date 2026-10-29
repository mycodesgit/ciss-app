<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use PDF;

use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\ExamineeResult;

/**
 * "Generate Pre-Enrollment" (Examination Results and Confirmed Applicants): the applicant's
 * Pre-Enrollment Form as a PDF (barryvdh/laravel-dompdf). This route is the src of the
 * Pre-Enrollment modal's iframe, so the browser shows it in its PDF viewer (zoom, download, print).
 */
class AdmissionPreEnrollmentController extends Controller
{
    use ManagesApplicants;

    public function show(Applicant $applicant)
    {
        $this->authorizeCampus($applicant);

        // Only applicants that already have an exam result (and were not cancelled)
        abort_if(
            (int) $applicant->p_status < Applicant::STATUS_WITH_RESULT || (int) $applicant->p_status === Applicant::STATUS_CANCELLED,
            404
        );

        $applicant->load(['result', 'deptRating']);

        return Pdf::loadView('pdf.pre-enrollment', [
            'applicant' => $applicant,
            'stanine' => ExamineeResult::stanineFor($applicant->result?->raw_score),
            'remarks' => $applicant->result?->percentile,
            'course' => $applicant->deptRating?->course,
            'interview' => $applicant->deptRating,
            'colleges' => config('ciss.pre_enrollment.colleges'),
            'bsedMajors' => config('ciss.pre_enrollment.bsed_majors'),
            'nurse' => config('ciss.pre_enrollment.nurses')[$applicant->campus] ?? ['name' => null, 'title' => 'Nurse'],
        ])
            ->setPaper('legal', 'portrait')
            ->stream('pre-enrollment-' . $applicant->admission_id . '.pdf');
    }
}
