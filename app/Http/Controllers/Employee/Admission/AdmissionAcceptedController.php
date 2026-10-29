<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use Illuminate\Http\Request;

use App\Models\AdmissionDB\Applicant;
use App\Models\EnrollmentDB\Student;

/**
 * Admission > Accepted Applicants: applicants accepted after the interview (p_status 5) and those already
 * pushed to enrollment (p_status 6). "Push Enrollment" creates the student record with a new Student ID.
 * Shows the applicants accepted by the user's department (administrators see all).
 * (public/js/employee/admission/accepted.js)
 */
class AdmissionAcceptedController extends Controller
{
    use ManagesApplicants;

    public function index()
    {
        return view('pages.employee.admission.accepted.list', $this->searchOptions());
    }

    // DataTables source, filtered by the search form
    public function list(Request $request)
    {
        $user = $this->user();

        $applicants = $this->searchApplicants($request, [Applicant::STATUS_ACCEPTED, Applicant::STATUS_ENROLLMENT])
            ->with(['deptRating', 'student:id,app_id,stud_id'])
            ->unless($user->isAdministrator(), fn ($query) => $query->whereHas('deptRating', fn ($q) => $q->where('deptcol', $user->dept)))
            ->get()
            ->map(fn (Applicant $applicant) => $this->present($applicant) + [
                'interviewer' => $applicant->deptRating?->interviewer,
                'accepted_course' => $applicant->deptRating?->course,
                'stud_id' => $applicant->student?->stud_id,
                'pushed' => (int) $applicant->p_status === Applicant::STATUS_ENROLLMENT,
            ]);

        return response()->json(['data' => $applicants]);
    }

    // Push Enrollment: create the student record (new Student ID) and mark the applicant as pushed
    public function pushToEnrollment(Applicant $applicant)
    {
        $this->authorizeCampus($applicant);

        if ((int) $applicant->p_status !== Applicant::STATUS_ACCEPTED) {
            return $this->fail('This applicant was already pushed to enrollment or is not accepted yet.');
        }

        $alreadyStudent = Student::where('app_id', $applicant->id)
            ->orWhere(fn ($query) => $query
                ->where('fname', $applicant->fname)
                ->where('mname', $applicant->mname)
                ->where('lname', $applicant->lname)
                ->where('campus', $applicant->campus))
            ->exists();

        if ($alreadyStudent) {
            return $this->fail('A student with the same name already exists in this campus.');
        }

        $student = Student::create($applicant->only([
            'status', 'type', 'campus', 'lname', 'fname', 'mname', 'ext', 'gender', 'civil_status', 'contact',
            'email', 'religion', 'address', 'bday', 'monthly_income', 'hnum', 'brgy', 'city', 'province',
            'region', 'zcode', 'lstsch_attended', 'suc_lst_attended',
        ]) + [
            'stud_id' => $this->nextStudentId($applicant),
            'app_id' => $applicant->id,
            'en_status' => 2,
            'p_status' => Applicant::STATUS_ENROLLMENT,
            'date_admission' => $applicant->d_admission,
        ]);

        $applicant->update(['en_status' => 2, 'p_status' => Applicant::STATUS_ENROLLMENT]);

        return $this->ok('Applicant can now proceed to enrollment. Student ID: ' . $student->stud_id);
    }

    // {year}-{0001}-{campus letter}, e.g. 2027-0001-K; the number continues from the last one of that year and campus
    private function nextStudentId(Applicant $applicant): string
    {
        $year = substr($applicant->admission_id, 0, 4);
        $letter = $this->studentIdLetter($applicant->campus);

        $lastId = Student::where('stud_id', 'like', "{$year}-%-{$letter}")->orderByDesc('stud_id')->value('stud_id');
        $next = $lastId ? (int) substr($lastId, 5, 4) + 1 : 1;

        return $year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT) . '-' . $letter;
    }

    private function studentIdLetter(string $campus): string
    {
        // Graduate School Staff (role 15) at Main Campus have their own letter
        if ($campus === 'MC' && (int) $this->user()->role === 15) {
            return config('ciss.graduate_school_letter');
        }

        return config('ciss.student_id_letters')[$campus] ?? 'X';
    }
}
