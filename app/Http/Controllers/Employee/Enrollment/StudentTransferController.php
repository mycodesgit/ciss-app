<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\AdmissionDB\User;
use App\Models\AssessmentDB\StudentAppraisal;
use App\Models\EnrollmentDB\Grade;
use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Models\EnrollmentDB\StudentTransfered;
use App\Models\SettingDB\ConfigureCurrent;
use App\Support\CampusList;

/**
 * Enrollment > Transferred Student (Registrar and Administrators: 'registrar' middleware).
 * Transfers a student of the user's campus to another campus. Like coas-app, the new campus is added to the
 * campus of the student's records, so both campuses keep seeing them ("MC" -> "MC, VC"):
 * students, program_en_history, studgrades and student_appraisal. Logged in studenttransfer.
 *  - index():   the page (transfer form + list of transfers)
 *  - list():    transfers from / to the user's campus (administrators: all)
 *  - lookup():  AJAX, the student to transfer (name, campuses, last enrollment, where they can go)
 *  - store():   the transfer (one transaction; refused if the student is already in that campus)
 * (public/js/employee/enrollment/student-transfer.js)
 */
class StudentTransferController extends Controller
{
    use ChecksEnrollment;

    public function index()
    {
        return view('pages.employee.enrollment.transfer.index', [
            'campuses' => config('ciss.campuses'),
        ]);
    }

    public function list()
    {
        $transfers = StudentTransfered::query()
            ->unless($this->user()->isAdministrator(), fn ($query) => $query->where(fn ($where) => $where
                ->whereIn('fromcampus', $this->campuses())->orWhereIn('tocampus', $this->campuses())))
            ->orderByDesc('id')
            ->get(['id', 'stud_id', 'fromcampus', 'tocampus', 'transferby', 'created_at']);

        $students = Student::whereIn('stud_id', $transfers->pluck('stud_id'))->get(['stud_id', 'lname', 'fname', 'mname', 'ext'])->keyBy('stud_id');
        $users = User::whereIn('id', $transfers->pluck('transferby'))->get(['id', 'fname', 'lname'])->keyBy('id');

        return response()->json([
            'data' => $transfers->map(fn ($transfer) => [
                'id' => $transfer->id,
                'stud_id' => $transfer->stud_id,
                'name' => $this->fullName($students[$transfer->stud_id] ?? null),
                'from' => $this->campusName($transfer->fromcampus),
                'to' => $this->campusName($transfer->tocampus),
                'date' => $transfer->created_at?->format('F d, Y h:i A'),
                'date_sort' => $transfer->created_at?->timestamp,
                'by' => ($by = $users[$transfer->transferby] ?? null) ? trim($by->fname . ' ' . $by->lname) : '',
            ]),
        ]);
    }

    // The student to transfer, with what the form needs to show before confirming
    public function lookup(Request $request)
    {
        $student = $this->transferableStudent($request);
        $campuses = CampusList::split($student->campus);
        $term = ConfigureCurrent::active();

        $last = StudEnrolmentHistory::where('studentID', $student->stud_id)->orderByDesc('id')->first(['schlyear', 'semester', 'course', 'campus']);

        return response()->json([
            'stud_id' => $student->stud_id,
            'name' => $this->fullName($student),
            'campuses' => array_map(fn ($code) => $this->campusName($code), $campuses),
            'from' => CampusList::current($student->campus),
            'fromName' => $this->campusName(CampusList::current($student->campus)),
            // Every campus the student isn't in yet
            'destinations' => collect(config('ciss.campuses'))->except($campuses)->map(fn ($name, $code) => ['code' => $code, 'name' => str_replace(' Campus', '', $name)])->values(),
            'lastEnrollment' => $last ? trim($last->course . ' · ' . $last->schlyear . ' ' . (ConfigureCurrent::SEMESTERS[(int) $last->semester] ?? '')) : null,
            // Enrolled in the active term: the transfer adds the new campus to that enrollment too (a warning)
            'enrolledNow' => $term && StudEnrolmentHistory::where('studentID', $student->stud_id)
                ->where('schlyear', $term->schlyear)->where('semester', $term->semester)->exists(),
        ]);
    }

    public function store(Request $request)
    {
        $student = $this->transferableStudent($request);
        $to = $request->validate([
            'tocampus' => ['required', Rule::in(array_keys(config('ciss.campuses')))],
        ], [], ['tocampus' => 'campus to transfer to'])['tocampus'];

        if (CampusList::has($student->campus, $to)) {
            $this->failEnrollment("{$student->stud_id} is already in {$this->campusName($to)}.");
        }

        $from = CampusList::current($student->campus);
        $user = $this->user();

        // The code is one of config('ciss.campuses') (checked above) and is quoted, never pasted into the SQL
        $append = fn ($connection) => DB::raw("CONCAT(campus, ', ', " . DB::connection($connection)->getPdo()->quote($to) . ')');
        $notYet = "FIND_IN_SET(?, REPLACE(campus, ' ', '')) = 0";

        DB::connection('enrollment')->transaction(fn () => DB::connection('assessment')->transaction(function () use ($student, $from, $to, $user, $append, $notYet) {
            $locked = Student::whereKey($student->id)->lockForUpdate()->first();
            if (CampusList::has($locked->campus, $to)) {
                $this->failEnrollment("{$student->stud_id} is already in {$this->campusName($to)}.");
            }

            StudentTransfered::create([
                'stud_id' => $student->stud_id,
                'studbaseprim_id' => $student->id,
                'fromcampus' => $from,
                'tocampus' => $to,
                'transferby' => $user->id,
            ]);

            Student::whereKey($student->id)->update(['campus' => $append('enrollment')]);
            StudEnrolmentHistory::where('studentID', $student->stud_id)->whereRaw($notYet, [$to])->update(['campus' => $append('enrollment')]);
            Grade::where('studID', $student->stud_id)->whereRaw($notYet, [$to])->update(['campus' => $append('enrollment')]);
            StudentAppraisal::where('studID', $student->stud_id)->whereRaw($notYet, [$to])->update(['campus' => $append('assessment')]);
        }));

        return response()->json([
            'success' => true,
            'message' => "{$student->stud_id} is transferred from {$this->campusName($from)} to {$this->campusName($to)}.",
        ]);
    }

    // ---------- Rules ----------

    // A student of the user's campus (administrators: any campus)
    private function transferableStudent(Request $request): Student
    {
        $request->merge(['stud_id' => strtoupper(trim((string) $request->input('stud_id')))]);
        $data = $request->validate(['stud_id' => ['required', 'regex:' . $this->studentIdPattern]],
            ['stud_id.regex' => 'The Student ID Number must look like 2024-0001-K.'], ['stud_id' => 'Student ID Number']);

        $student = Student::where('stud_id', $data['stud_id'])
            ->unless($this->user()->isAdministrator(), fn ($query) => $query->whereCampusIn('campus', $this->campuses()))
            ->first(['id', 'stud_id', 'lname', 'fname', 'mname', 'ext', 'campus']);

        if (! $student) {
            $this->failEnrollment("Student ID Number {$data['stud_id']} does not exist in your campus.");
        }

        return $student;
    }

    private function campusName(?string $code): string
    {
        return str_replace(' Campus', '', config('ciss.campuses')[$code] ?? (string) $code);
    }

    private function fullName(?Student $student): string
    {
        if (! $student) {
            return '';
        }

        $middle = $student->mname ? mb_substr($student->mname, 0, 1) . '.' : '';
        $ext = $student->ext && $student->ext !== 'N/A' ? ' ' . $student->ext : '';

        return trim("{$student->lname}, {$student->fname} {$middle}{$ext}");
    }
}
