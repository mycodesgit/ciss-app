<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\ShowsQueuePanel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\AdmissionDB\User;
use App\Models\AssessmentDB\StudentAppraisal;
use App\Models\EnrollmentDB\Grade;
use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Enrollment > Edit Dup. Fees: remove fee lines that were saved twice in a student's appraisal of a term.
 *  - index() / find(): search page (campus, Student ID, school year, semester), queue panel, pre-enrolled list
 *  - show():           the student, the subject totals and the fee lines (loaded by list())
 *  - list():           the appraisal lines of the enrollment, duplicates marked
 *  - destroy():        deletes one duplicated line (never the last copy of a fee)
 * Campus: administrators can pick any campus, everyone else only their own. (enroll.js, dup-fees.js)
 */
class DuplicateFeesController extends Controller
{
    use ChecksEnrollment, ShowsQueuePanel;

    public function index()
    {
        return view('pages.employee.enrollment.dupfees.search', $this->queuePanelData() + [
            'campuses' => $this->searchableCampuses(),
            'userCampus' => $this->user()->campus,
            'schoolYears' => ConfigureCurrent::orderByDesc('id')->pluck('schlyear')->unique()->values(),
            'semesters' => collect(array_keys(ConfigureCurrent::SEMESTERS)),
        ]);
    }

    public function find(Request $request)
    {
        [$student, $term, $enrollment] = $this->enrollmentOf($request);

        return response()->json(['url' => route('employee.enrollment.dup-fees.show', [
            'student' => $student->stud_id,
            'campus' => $enrollment->campus,
            'schlyear' => $term->schlyear,
            'semester' => $term->semester,
        ])]);
    }

    public function show(Request $request, string $student)
    {
        try {
            [$student, $term, $enrollment] = $this->enrollmentOf($request->merge(['stud_id' => $student]));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('employee.enrollment.dup-fees.index')->with('error', collect($e->errors())->flatten()->first());
        }

        $schedule = config('database.connections.schedule.database');
        $subjects = Grade::join("{$schedule}.sub_offered as sub_offered", 'studgrades.subjID', '=', 'sub_offered.id')
            ->where('studgrades.studID', $student->stud_id)
            ->where('sub_offered.schlyear', $term->schlyear)
            ->where('sub_offered.semester', $term->semester)
            ->where('sub_offered.campus', $enrollment->campus)
            ->get(['sub_offered.lecFee', 'sub_offered.labFee']);

        $poster = User::find($enrollment->postedBy);
        $query = ['student' => $student->stud_id, 'campus' => $enrollment->campus, 'schlyear' => $term->schlyear, 'semester' => $term->semester];

        return view('pages.employee.enrollment.dupfees.show', [
            'student' => $student,
            'term' => $term,
            'enrollment' => $enrollment,
            'totalLecFee' => $subjects->sum(fn ($subject) => (float) $subject->lecFee),
            'totalLabFee' => $subjects->sum(fn ($subject) => (float) $subject->labFee),
            'postedByName' => $poster ? trim($poster->fname . ' ' . $poster->lname) : '',
            'listUrl' => route('employee.enrollment.dup-fees.list', $query),
            'deleteUrl' => route('employee.enrollment.dup-fees.destroy', $query + ['appraisal' => '__ID__']),
            'rfUrl' => route('employee.enrollment.enroll.registration-form', $query),
        ]);
    }

    // The enrollment's appraisal lines; a line is a duplicate when its fund + account appear more than once
    public function list(Request $request, string $student)
    {
        [$student, $term, $enrollment] = $this->enrollmentOf($request->merge(['stud_id' => $student]));

        $lines = $this->appraisal($student, $term, $enrollment->campus)->orderBy('account')->orderBy('id')->get(['id', 'fundID', 'account', 'amount']);
        $copies = $lines->countBy(fn ($line) => $this->feeKey($line));

        return response()->json([
            'data' => $lines->map(fn ($line) => [
                'id' => $line->id,
                'fundID' => $line->fundID,
                'account' => $line->account,
                'amount' => (float) $line->amount,
                'copies' => $copies[$this->feeKey($line)],
                'duplicate' => $copies[$this->feeKey($line)] > 1,
            ]),
            'total' => (float) $lines->sum('amount'),
        ]);
    }

    // Deletes one copy of a duplicated fee line of this enrollment (the last copy always stays)
    public function destroy(Request $request, string $student, int $appraisal)
    {
        [$student, $term, $enrollment] = $this->enrollmentOf($request->merge(['stud_id' => $student]));

        DB::connection('assessment')->transaction(function () use ($student, $term, $enrollment, $appraisal) {
            $lines = $this->appraisal($student, $term, $enrollment->campus)->lockForUpdate()->get(['id', 'fundID', 'account']);

            // Only a line of this student's term and campus (any other id is refused)
            $line = $lines->firstWhere('id', $appraisal);
            abort_unless($line, 404, 'Fee line not found in this enrollment.');

            $copies = $lines->filter(fn ($other) => $this->feeKey($other) === $this->feeKey($line))->count();
            if ($copies < 2) {
                $this->failEnrollment("{$line->account} is not a duplicate, so it can't be deleted here. Use Edit Enrollment to re-assess the fees.");
            }

            StudentAppraisal::whereKey($line->id)->delete();
        });

        return response()->json(['success' => true, 'message' => 'Duplicate fee line deleted.']);
    }

    // ---------- Rules ----------

    /**
     * The student, term and enrollment of the request (campus, stud_id, schlyear, semester):
     * the campus must be one the user may search, and the student must be enrolled there in that term.
     */
    private function enrollmentOf(Request $request): array
    {
        $request->merge(['stud_id' => strtoupper(trim((string) $request->input('stud_id')))]);

        $data = $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'stud_id' => ['required', 'regex:' . $this->studentIdPattern],
            'schlyear' => ['required', 'string'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ], ['stud_id.regex' => 'The Student ID Number must look like 2024-0001-K.'], ['stud_id' => 'Student ID Number']);

        $student = Student::where('stud_id', $data['stud_id'])->where('campus', $data['campus'])->first();
        if (! $student) {
            $this->failEnrollment("Student ID Number {$data['stud_id']} does not exist in this campus.");
        }

        $term = ConfigureCurrent::where('schlyear', $data['schlyear'])->where('semester', $data['semester'])->first()
            ?? new ConfigureCurrent(['schlyear' => $data['schlyear'], 'semester' => (int) $data['semester']]);

        $enrollment = StudEnrolmentHistory::where('studentID', $student->stud_id)
            ->where('schlyear', $term->schlyear)
            ->where('semester', $term->semester)
            ->where('campus', $data['campus'])
            ->first();

        if (! $enrollment) {
            $this->failEnrollment("Student ID Number {$student->stud_id} is not enrolled in {$term->label}.");
        }

        return [$student, $term, $enrollment];
    }

    // Administrators: every campus; everyone else: their own campus(es)
    private function searchableCampuses(): array
    {
        $campuses = config('ciss.campuses');

        return $this->user()->isAdministrator() ? $campuses : array_intersect_key($campuses, array_flip($this->campuses()));
    }

    private function appraisal(Student $student, ConfigureCurrent $term, string $campus)
    {
        return StudentAppraisal::where('studID', $student->stud_id)
            ->where('schlyear', $term->schlyear)
            ->where('semester', $term->semester)
            ->where('campus', $campus);
    }

    // Lines with the same fund and account are copies of the same fee
    private function feeKey($line): string
    {
        return strtoupper(trim((string) $line->fundID)) . '|' . strtoupper(trim((string) $line->account));
    }
}
