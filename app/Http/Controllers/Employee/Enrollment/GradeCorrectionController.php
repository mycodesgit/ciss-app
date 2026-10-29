<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\ReadsGradesheet;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Models\EnrollmentDB\Grade;
use App\Models\SettingDB\ConfigureCurrent;
use App\Models\SettingDB\GradePass;
use App\Services\Enrollment\GradeRules;

/**
 * Enrollment > Correction of Grades (Registrar and Administrators, every campus: 'registrar' middleware).
 * Like the Gradesheet, but any final grade and completion can be changed, even submitted ones and INC.
 * Every row is locked: unlock() with the campus grades password unlocks one row for UNLOCK_MINUTES (kept in the
 * user's session, so it can't be skipped by calling the correction URL); saving a correction locks it again.
 * A corrected grade keeps its state (a submitted grade stays submitted).
 *  - index() / subjects(), show() / students(): like the Gradesheet
 *  - unlock():            the grades password -> the row can be corrected for a few minutes
 *  - correctGrade():      changes a final grade (an INC changed to a regular grade drops its completion)
 *  - correctCompletion(): changes a completion (for an INC / FAILURE grade, or one that has a completion)
 * Credits follow the grade that counts: the completion when there is one, otherwise the final grade.
 * Every correction is logged in studgrades_logs. Shared rules: Concerns/ReadsGradesheet. (grade-correction.js)
 */
class GradeCorrectionController extends Controller
{
    use ChecksEnrollment, ReadsGradesheet;

    private const UNLOCK_MINUTES = 5;
    private const SESSION_KEY = 'grade_corrections_unlocked'; // [studgrades id => unlocked until (timestamp)]

    public function index()
    {
        return view('pages.employee.enrollment.correction.index', [
            'schoolYears' => ConfigureCurrent::orderByDesc('id')->pluck('schlyear')->unique()->values(),
            'semesters' => ConfigureCurrent::SEMESTERS,
        ]);
    }

    public function subjects(Request $request)
    {
        return $this->subjectsResponse($request, 'employee.enrollment.corrections.show');
    }

    public function show(int $subject)
    {
        $subject = $this->subject($subject);

        return view('pages.employee.enrollment.correction.show', [
            'subject' => $subject,
            'term' => $this->termOf($subject),
            'gradeCodes' => GradeRules::codesFor($this->user()),
        ]);
    }

    public function students(int $subject)
    {
        $subject = $this->subject($subject);

        return response()->json(['data' => $this->gradeRows($subject)->map(fn ($row) => $this->present($row))]);
    }

    // The campus grades password unlocks this row (for this user's session) for UNLOCK_MINUTES
    public function unlock(Request $request, int $grade)
    {
        [$row, $subject] = $this->gradeRow($grade);

        $password = (string) $request->validate(['password' => ['required', 'string']], [], ['password' => 'grades password'])['password'];
        if (! GradePass::matches($subject->campus, $password)) {
            throw ValidationException::withMessages(['password' => 'Wrong grades password.']);
        }

        $unlocked = $this->unlockedRows();
        $unlocked[$row->id] = now()->addMinutes(self::UNLOCK_MINUTES)->timestamp;
        $request->session()->put(self::SESSION_KEY, $unlocked);

        return $this->rowResponse($row->id, $subject, 'Unlocked for ' . self::UNLOCK_MINUTES . ' minutes. It locks again after you save a correction.');
    }

    public function correctGrade(Request $request, int $grade)
    {
        [$row, $subject] = $this->gradeRow($grade);
        $this->ensureUnlocked($row);
        $value = $this->validatedGrade($request);

        // A grade that no longer takes a completion loses the old one
        $keepsCompletion = $value !== null && GradeRules::needsCompletion($value);
        $completion = $keepsCompletion ? ($row->subjComp ?: null) : null;

        Grade::whereKey($row->id)->update([
            'subjFgrade' => $value ?? '',
            'status' => $this->correctedStatus($value, $row->status),
            'subjComp' => $completion ?? '',
            'compstat' => $completion === null ? null : $row->compstat,
            'creditEarned' => GradeRules::credit($completion ?? $value, $subject->subUnit),
        ]);

        $this->logGrade($row, ['fgrade' => $value ?? '', 'datefgrade' => now(), 'cgrade' => $completion ?? '']);
        $this->lock($row);

        $message = "Grade of {$row->studID} corrected to " . ($value ?? '(no grade)') . '.';
        if ($row->subjComp && $completion === null) {
            $message .= " The completion {$row->subjComp} was removed.";
        }

        return $this->rowResponse($row->id, $subject, $message);
    }

    public function correctCompletion(Request $request, int $grade)
    {
        [$row, $subject] = $this->gradeRow($grade);
        $this->ensureUnlocked($row);
        $value = $this->validatedGrade($request);

        if (! GradeRules::needsCompletion($row->subjFgrade) && ! $row->subjComp) {
            $this->failGrade('A completion grade is only for an INC or FAILURE final grade.');
        }

        Grade::whereKey($row->id)->update([
            'subjComp' => $value ?? '',
            'compstat' => $this->correctedStatus($value, $row->compstat),
            'creditEarned' => GradeRules::credit($value ?? $row->subjFgrade, $subject->subUnit),
        ]);

        $this->logGrade($row, ['fgrade' => $row->subjFgrade, 'cgrade' => $value ?? '', 'datecgrade' => now()]);
        $this->lock($row);

        return $this->rowResponse($row->id, $subject, "Completion of {$row->studID} corrected to " . ($value ?? '(none)') . '.');
    }

    // ---------- Unlock (session) ----------

    // Unlocked rows of this session that haven't expired
    private function unlockedRows(): array
    {
        return array_filter((array) session(self::SESSION_KEY, []), fn ($until) => $until > now()->timestamp);
    }

    private function isUnlocked(int $id): bool
    {
        return isset($this->unlockedRows()[$id]);
    }

    private function ensureUnlocked($row): void
    {
        if (! $this->isUnlocked($row->id)) {
            $this->failGrade('This grade is locked. Unlock it with the grades password first.');
        }
    }

    private function lock($row): void
    {
        $unlocked = $this->unlockedRows();
        unset($unlocked[$row->id]);
        session()->put(self::SESSION_KEY, $unlocked);
    }

    // ---------- Helpers ----------

    // Cleared -> no status; a submitted grade stays submitted; otherwise encoded
    private function correctedStatus(?string $value, $current): ?int
    {
        if ($value === null) {
            return null;
        }

        return (int) $current === $this->submittedStatus ? $this->submittedStatus : $this->encodedStatus;
    }

    private function present($row): array
    {
        return $this->rowBasics($row) + [
            'completionShown' => GradeRules::needsCompletion($row->subjFgrade) || (bool) $row->subjComp,
            'unlocked' => $this->isUnlocked($row->id),
            'unlockUrl' => route('employee.enrollment.corrections.unlock', $row->id),
            'correctGradeUrl' => route('employee.enrollment.corrections.grade', $row->id),
            'correctCompletionUrl' => route('employee.enrollment.corrections.completion', $row->id),
        ];
    }

    private function rowResponse(int $id, $subject, string $message)
    {
        $row = $this->gradeRows($subject)->firstWhere('id', $id);

        return response()->json(['success' => true, 'message' => $message, 'row' => $this->present($row)]);
    }
}
