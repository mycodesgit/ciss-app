<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use App\Models\AdmissionDB\Applicant;
use App\Services\Admission\PreEnrollmentForm;

/**
 * "Generate Pre-Enrollment" (Examination Results and Confirmed Applicants): the applicant's
 * Pre-Enrollment Form as a PDF (barryvdh/laravel-dompdf, built by Services/Admission/PreEnrollmentForm).
 * This route is the src of the Pre-Enrollment modal's iframe, so the browser shows it in its PDF viewer (zoom, download, print).
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

        return PreEnrollmentForm::forApplicant($applicant)->stream('pre-enrollment-' . $applicant->admission_id . '.pdf');
    }
}
