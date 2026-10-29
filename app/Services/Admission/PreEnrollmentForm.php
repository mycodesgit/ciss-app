<?php

namespace App\Services\Admission;

use PDF;

use App\Models\AdmissionDB\Applicant;
use App\Models\AdmissionDB\ExamineeResult;
use App\Models\AdmissionDB\Programs;
use App\Models\EnrollmentDB\Student;

/**
 * The Pre-Enrollment Form PDF (resources/views/pdf/pre-enrollment.blade.php), Legal portrait.
 * Printed from Admission (Examination Results, Confirmed / Accepted Applicants) and from Enrollment History.
 */
class PreEnrollmentForm
{
    // The form of an applicant: personal data, exam result and the department interview (checked course)
    public static function forApplicant(Applicant $applicant)
    {
        $applicant->loadMissing(['result', 'deptRating']);

        return PDF::loadView('pdf.pre-enrollment', [
            'applicant' => $applicant,
            'stanine' => ExamineeResult::stanineFor($applicant->result?->raw_score),
            'remarks' => $applicant->result?->percentile,
            'course' => $applicant->deptRating?->course,
            // Name of the interviewed course (ad_programs), so the form can also match it by name
            'courseName' => $applicant->deptRating?->course ? Programs::where('code', $applicant->deptRating->course)->value('program') : null,
            'interview' => $applicant->deptRating,
            'colleges' => config('ciss.pre_enrollment.colleges'),
            'bsedMajors' => config('ciss.pre_enrollment.bsed_majors'),
            'nurse' => config('ciss.pre_enrollment.nurses')[$applicant->campus] ?? ['name' => null, 'title' => 'Nurse'],
        ])->setPaper('legal', 'portrait');
    }

    /**
     * The form of an enrolled student: from the student's admission record (students.app_id) when there is one;
     * otherwise from the student record itself, with the exam and interview parts left blank (like the old form).
     */
    public static function forStudent(Student $student)
    {
        $applicant = $student->app_id ? Applicant::find($student->app_id) : null;

        if (! $applicant) {
            $applicant = (new Applicant())->forceFill($student->only([
                'lname', 'fname', 'mname', 'ext', 'gender', 'bday', 'address', 'campus', 'type',
            ]));
            $applicant->setRelation('result', null);
            $applicant->setRelation('deptRating', null);
        }

        return self::forApplicant($applicant);
    }
}
