<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\ReadsGradesheet;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use App\Models\EnrollmentDB\Grade;
use App\Models\SettingDB\ConfigureCurrent;
use App\Models\SettingDB\GradePass;
use App\Services\Enrollment\GradeRules;

/**
 * Enrollment > Gradesheet: encode, submit and unlock the grades of a subject offered in the user's campus.
 *  - index() / subjects():    pick a term, list its subjects (Graduate School staff: GSS subjects; others: the rest)
 *  - show() / students():     the gradesheet of one subject
 *  - saveGrade():             encodes a final grade (not when submitted)
 *  - saveCompletion():        encodes a completion (only for an INC / FAILURE final grade, not when submitted)
 *  - submit():                locks the subject's encoded grades and completions
 *  - unlockGrade() / unlockCompletion(): with the campus grades password, makes a submitted grade editable again
 * Shared rules (subject access, rows, grade codes, logs): Concerns/ReadsGradesheet. Correcting a submitted grade
 * directly is Correction of Grades (GradeCorrectionController). (public/js/employee/enrollment/gradesheet.js)
 * The NSTP module's Grade Sheet reuses all of this for NSTP subjects: Nstp\NstpGradesheetController sets the
 * properties below and narrows allowedSubjects().
 */
class GradesheetController extends Controller
{
    use ChecksEnrollment, ReadsGradesheet;

    protected string $routeName = 'employee.enrollment.gradesheet';
    protected array $module = ['name' => 'Enrollment', 'layout' => 'layouts.master_empenrollment', 'description' => 'Encode and view student grades.'];

    public function index()
    {
        return view('pages.employee.enrollment.gradesheet.index', [
            'schoolYears' => ConfigureCurrent::orderByDesc('id')->pluck('schlyear')->unique()->values(),
            'semesters' => ConfigureCurrent::SEMESTERS,
            'module' => $this->module,
            'routeName' => $this->routeName,
        ]);
    }

    public function subjects(Request $request)
    {
        return $this->subjectsResponse($request, "{$this->routeName}.show");
    }

    public function show(int $subject)
    {
        $subject = $this->subject($subject);

        return view('pages.employee.enrollment.gradesheet.show', [
            'subject' => $subject,
            'term' => $this->termOf($subject),
            'gradeCodes' => GradeRules::codesFor($this->user()),
            'module' => $this->module,
            'routeName' => $this->routeName,
        ]);
    }

    public function students(int $subject)
    {
        $subject = $this->subject($subject);

        return response()->json([
            'data' => $this->gradeRows($subject)->map(fn ($row) => $this->present($row)),
            'pending' => $this->pendingCount($subject),
        ]);
    }

    public function saveGrade(Request $request, int $grade)
    {
        [$row, $subject] = $this->gradeRow($grade);
        $value = $this->validatedGrade($request);

        // Only while not submitted (checked in the update itself, so a submit in between can't be overwritten)
        $updated = Grade::whereKey($row->id)
            ->where(fn ($query) => $query->whereNull('status')->orWhere('status', '!=', $this->submittedStatus))
            ->update([
                'subjFgrade' => $value ?? '',
                'status' => $value === null ? null : $this->encodedStatus,
                'creditEarned' => GradeRules::credit($value, $subject->subUnit),
            ]);

        if (! $updated) {
            $this->failGrade('This grade is already submitted. Use Edit Grades to change it.');
        }

        $this->logGrade($row, ['fgrade' => $value ?? '', 'datefgrade' => now()]);

        return $this->rowResponse($row->id, $subject, 'Grade saved.');
    }

    public function saveCompletion(Request $request, int $grade)
    {
        [$row, $subject] = $this->gradeRow($grade);
        $value = $this->validatedGrade($request);

        if (! GradeRules::needsCompletion($row->subjFgrade)) {
            $this->failGrade('A completion grade is only for an INC or FAILURE final grade.');
        }

        $updated = Grade::whereKey($row->id)
            ->where(fn ($query) => $query->whereNull('compstat')->orWhere('compstat', '!=', $this->submittedStatus))
            ->update([
                'subjComp' => $value ?? '',
                'compstat' => $value === null ? null : $this->encodedStatus,
                'creditEarned' => GradeRules::credit($value, $subject->subUnit),
            ]);

        if (! $updated) {
            $this->failGrade('This completion is already submitted. Use Completion to change it.');
        }

        $this->logGrade($row, ['fgrade' => $row->subjFgrade, 'cgrade' => $value ?? '', 'datecgrade' => now()]);

        return $this->rowResponse($row->id, $subject, 'Completion saved.');
    }

    // Locks every encoded grade and completion of the subject
    public function submit(int $subject)
    {
        $subject = $this->subject($subject);
        $user = $this->user();

        $submitted = DB::connection('enrollment')->transaction(function () use ($subject, $user) {
            $grades = Grade::where('subjID', $subject->id)->where('status', $this->encodedStatus)
                ->update(['status' => $this->submittedStatus, 'postedBy' => $user->id]);
            $completions = Grade::where('subjID', $subject->id)->where('compstat', $this->encodedStatus)
                ->update(['compstat' => $this->submittedStatus]);

            return $grades + $completions;
        });

        if (! $submitted) {
            $this->failGrade('There are no encoded grades to submit.');
        }

        return response()->json(['success' => true, 'message' => "{$submitted} grade(s) submitted. They are now locked."]);
    }

    public function unlockGrade(Request $request, int $grade)
    {
        [$row, $subject] = $this->gradeRow($grade);
        $this->checkPassword($request, $subject);

        if ((int) $row->status !== $this->submittedStatus) {
            $this->failGrade('This grade is not submitted; it can be edited already.');
        }
        if (GradeRules::needsCompletion($row->subjFgrade)) {
            $this->failGrade('An INC / FAILURE grade is changed through its completion (or in Correction of Grades).');
        }

        Grade::whereKey($row->id)->update(['status' => $this->encodedStatus]);

        return $this->rowResponse($row->id, $subject, 'Now you can edit the grade. Submit again when done.');
    }

    public function unlockCompletion(Request $request, int $grade)
    {
        [$row, $subject] = $this->gradeRow($grade);
        $this->checkPassword($request, $subject);

        if ((int) $row->compstat !== $this->submittedStatus) {
            $this->failGrade('This completion is not submitted; it can be edited already.');
        }

        Grade::whereKey($row->id)->update(['compstat' => $this->encodedStatus]);

        return $this->rowResponse($row->id, $subject, 'Now you can edit the completion. Submit again when done.');
    }

    // ---------- Helpers ----------

    // The campus grades password (Accounts > Grades Password) of the subject's campus
    private function checkPassword(Request $request, $subject): void
    {
        $password = (string) $request->validate(['password' => ['required', 'string']], [], ['password' => 'grades password'])['password'];

        if (! GradePass::matches($subject->campus, $password)) {
            throw ValidationException::withMessages(['password' => 'Wrong grades password.']);
        }
    }

    // A row of the gradesheet table, with what may be done on it
    private function present($row): array
    {
        $gradeLocked = (int) $row->status === $this->submittedStatus;
        $needsCompletion = GradeRules::needsCompletion($row->subjFgrade);
        $completionLocked = (int) $row->compstat === $this->submittedStatus;

        return $this->rowBasics($row) + [
            'gradeEditable' => ! $gradeLocked,
            'completionShown' => $needsCompletion,
            'completionEditable' => $needsCompletion && ! $completionLocked,
            'canUnlockGrade' => $gradeLocked && ! $needsCompletion,
            'canUnlockCompletion' => $completionLocked,
            'saveGradeUrl' => route("{$this->routeName}.grade", $row->id),
            'saveCompletionUrl' => route("{$this->routeName}.completion", $row->id),
            'unlockGradeUrl' => route("{$this->routeName}.unlock-grade", $row->id),
            'unlockCompletionUrl' => route("{$this->routeName}.unlock-completion", $row->id),
        ];
    }

    // Encoded but not yet submitted grades / completions of the subject (Submit Grades is enabled when > 0)
    private function pendingCount($subject): int
    {
        return Grade::where('subjID', $subject->id)
            ->where(fn ($query) => $query->where('status', $this->encodedStatus)->orWhere('compstat', $this->encodedStatus))
            ->count();
    }

    private function rowResponse(int $id, $subject, string $message)
    {
        $row = $this->gradeRows($subject)->firstWhere('id', $id);

        return response()->json(['success' => true, 'message' => $message, 'row' => $this->present($row), 'pending' => $this->pendingCount($subject)]);
    }
}
