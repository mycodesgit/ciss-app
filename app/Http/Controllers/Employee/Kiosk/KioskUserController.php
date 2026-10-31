<?php

namespace App\Http\Controllers\Employee\Kiosk;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\EnrollmentDB\KioskUser;
use App\Models\EnrollmentDB\Student;
use App\Services\Enrollment\ClassAttendance;
use App\Services\Kiosk\KioskAccounts;

/**
 * Kiosk > Kiosk User: the students' kiosk accounts of a campus.
 *  - index():   the page (campus select, accounts table, Add / Reset Password modals)
 *  - list():    AJAX, the accounts of the campus (DataTable)
 *  - student(): AJAX, the name of a Student ID while it is typed in Add New (and whether it has an account)
 *  - store():   creates an account; reset(): sets a new password (resetnumber + 1); destroy(): deletes it
 * Each create / reset is a kiosk log of the employee (Dashboard). Accounts are reached through their student's
 * campus: administrators every campus, everyone else their own (FindsEnrollment). (kiosk/users.js)
 */
class KioskUserController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    public function index()
    {
        return view('pages.employee.kiosk.users.index', $this->searchOptions());
    }

    public function list(Request $request)
    {
        $campus = $request->validate(['campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))]])['campus'];

        // From students (filtered by campus) to kioskstudent, whose studid is indexed
        $accounts = Student::join('kioskstudent', 'kioskstudent.studid', '=', 'students.stud_id')
            ->whereCampusIn('students.campus', [$campus])
            ->orderBy('students.lname')->orderBy('students.fname')
            ->get(['kioskstudent.id', 'kioskstudent.studid', 'kioskstudent.resetnumber', 'kioskstudent.updated_at as changed_at',
                'students.lname', 'students.fname', 'students.mname', 'students.ext']);

        return response()->json([
            'data' => $accounts->map(function ($account) {
                $changedAt = $account->changed_at ? Carbon::parse($account->changed_at) : null;

                return [
                    'id' => $account->id,
                    'studid' => $account->studid,
                    'name' => ClassAttendance::fullName($account),
                    'resets' => (int) $account->resetnumber,
                    'changed_at' => $changedAt?->timestamp,
                    'changed_at_label' => $changedAt?->format('M d, Y h:i A'),
                ];
            }),
        ]);
    }

    public function student(string $studentId)
    {
        $student = $this->studentOfCampuses($studentId);

        return response()->json([
            'name' => ClassAttendance::fullName($student),
            'hasAccount' => KioskUser::where('studid', $student->stud_id)->exists(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge(['studid' => strtoupper(trim((string) $request->input('studid')))]);
        $data = $request->validate([
            'studid' => ['required', 'regex:' . $this->studentIdPattern],
            'password' => ['required', 'string', 'min:4', 'max:20'],
        ], ['studid.regex' => 'The Student ID Number must look like 2024-0001-K.'], ['studid' => 'Student ID Number']);

        $student = $this->studentOfCampuses($data['studid']);

        if (KioskUser::where('studid', $student->stud_id)->exists()) {
            throw ValidationException::withMessages(['studid' => "Student ID Number {$student->stud_id} already has a kiosk account. Use Reset Password."]);
        }

        KioskAccounts::setPassword($student->stud_id, strtoupper($data['password']), $this->user());

        return response()->json(['success' => true, 'message' => "Kiosk account of {$student->stud_id} created."]);
    }

    public function reset(Request $request, KioskUser $account)
    {
        $this->authorizeAccount($account);
        $password = $request->validate(['password' => ['required', 'string', 'min:4', 'max:20']])['password'];

        KioskAccounts::setPassword($account->studid, strtoupper($password), $this->user());

        return response()->json(['success' => true, 'message' => "Kiosk password of {$account->studid} reset."]);
    }

    public function destroy(KioskUser $account)
    {
        $this->authorizeAccount($account);
        $account->delete();

        return response()->json(['success' => true, 'message' => "Kiosk account of {$account->studid} deleted."]);
    }

    // ---------- Helpers ----------

    // The student with this Student ID in a campus the user may manage (422 otherwise)
    private function studentOfCampuses(string $studentId): Student
    {
        $studentId = strtoupper(trim($studentId));
        $student = Student::where('stud_id', $studentId)->whereCampusIn('campus', array_keys($this->searchableCampuses()))->first();

        if (! $student) {
            throw ValidationException::withMessages(['studid' => "Student ID Number {$studentId} does not exist in your campus."]);
        }

        return $student;
    }

    // Only accounts of students in a campus the user may manage
    private function authorizeAccount(KioskUser $account): void
    {
        $inCampus = Student::where('stud_id', $account->studid)->whereCampusIn('campus', array_keys($this->searchableCampuses()))->exists();

        abort_unless($inCampus, 404, 'Kiosk account not found in your campus.');
    }
}
