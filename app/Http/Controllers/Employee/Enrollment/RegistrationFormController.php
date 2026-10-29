<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PDF;

use App\Models\AdmissionDB\User;
use App\Models\AssessmentDB\StudentAppraisal;
use App\Models\AssessmentDB\StudPayment;
use App\Models\EnrollmentDB\Grade;
use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Models\ScheduleDB\EnPrograms;
use App\Models\ScholarshipDB\Scholar;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Print RF: the Registration Form (PDF) of an enrollment in the user's campus, the src of the PDF modal's iframe.
 * Subjects (studgrades + sub_offered), appraised fees (student_appraisal) and payments (studpayment) of the term.
 */
class RegistrationFormController extends Controller
{
    use ChecksEnrollment;

    // Campus header images in public/uilibs/images/studrf (Main Campus: studrfheader.png)
    private const HEADERS = ['VC', 'SCC', 'HC', 'MP', 'IC', 'CA', 'CC', 'SC', 'HinC'];

    public function show(Request $request, string $student)
    {
        $data = $request->validate([
            'schlyear' => ['required', 'string'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
            'campus' => ['nullable', Rule::in(array_keys(config('ciss.campuses')))],
        ]);

        // Administrators may print any campus (?campus=, e.g. from Edit Dup. Fees); everyone else only their own
        $campuses = $this->user()->isAdministrator() && ! empty($data['campus']) ? [$data['campus']] : $this->campuses();

        $enrollment = StudEnrolmentHistory::where('studentID', $student)
            ->where('schlyear', $data['schlyear'])
            ->where('semester', $data['semester'])
            ->whereIn('campus', $campuses)
            ->firstOrFail();

        $schedule = config('database.connections.schedule.database');

        $subjects = Grade::join("{$schedule}.sub_offered as sub_offered", 'studgrades.subjID', '=', 'sub_offered.id')
            ->join("{$schedule}.subjects as subjects", 'sub_offered.subCode', '=', 'subjects.sub_code')
            ->where('studgrades.studID', $enrollment->studentID)
            ->where('sub_offered.schlyear', $enrollment->schlyear)
            ->where('sub_offered.semester', $enrollment->semester)
            ->where('sub_offered.campus', $enrollment->campus)
            ->orderBy('sub_offered.subCode')
            ->get(['sub_offered.subCode', 'sub_offered.subSec', 'sub_offered.subUnit', 'sub_offered.lecFee', 'sub_offered.labFee', 'sub_offered.isType', 'subjects.sub_name', 'subjects.sub_title']);

        $fees = StudentAppraisal::where('studID', $enrollment->studentID)
            ->where('schlyear', $enrollment->schlyear)
            ->where('semester', $enrollment->semester)
            ->where('campus', $enrollment->campus)
            ->orderBy('account')
            ->get(['account', 'amount']);

        $payments = StudPayment::where('studID', $enrollment->studentID)
            ->where('schlyear', $enrollment->schlyear)
            ->where('semester', $enrollment->semester)
            ->get(['account', 'datepaid', 'orno', 'amountpaid']);

        $user = $this->user();
        $poster = User::find($enrollment->postedBy);

        return PDF::loadView('pdf.registration-form', [
            'enrollment' => $enrollment,
            'student' => Student::where('stud_id', $enrollment->studentID)->first(),
            'scholarship' => Scholar::whereKey($enrollment->studSch)->value('scholar_name'),
            'programAcronym' => EnPrograms::where('progCod', $enrollment->progCod)->value('progAcronym'),
            'semesterName' => strtoupper(ConfigureCurrent::SEMESTERS[(int) $enrollment->semester] ?? ''),
            'header' => in_array($enrollment->campus, self::HEADERS, true) ? "studrfheader-{$enrollment->campus}.png" : 'studrfheader.png',
            'subjects' => $subjects,
            'fees' => $fees,
            'payments' => $payments,
            // Graduate school: shows the subjects' cycle
            'showCycle' => $this->isGraduateStaff() || str_contains($enrollment->studentID, '-G'),
            // Like the old RF: administrators see who enrolled the student, others their own name
            'postedBy' => $user->isAdministrator() && $poster ? trim($poster->fname . ' ' . $poster->lname) : trim($user->fname . ' ' . $user->lname),
        ])
            ->setPaper('legal', 'portrait')
            ->stream('RF-' . $enrollment->studentID . '.pdf');
    }
}
