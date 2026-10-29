<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\ReadsGradesheet;

use Illuminate\Http\Request;

use App\Models\EnrollmentDB\Grade;
use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Enrollment\GradeRules;

/**
 * Enrollment > Correction of Grades (Registrar and Administrators, every campus: 'registrar' middleware).
 * Like the Gradesheet, but any final grade and completion can be changed directly, even submitted ones and INC,
 * without the grades password. A corrected grade keeps its state (a submitted grade stays submitted).
 *  - index() / subjects(), show() / students(): like the Gradesheet
 *  - correctGrade():      changes a final grade (an INC changed to a regular grade drops its completion)
 *  - correctCompletion(): changes a completion (for an INC / FAILURE grade, or one that has a completion)
 * Credits follow the grade that counts: the completion when there is one, otherwise the final grade.
 * Every correction is logged in studgrades_logs. Shared rules: Concerns/ReadsGradesheet. (grade-correction.js)
 */
class GradeCorrectionController extends Controller
{
    use ChecksEnrollment, ReadsGradesheet;

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

    public function correctGrade(Request $request, int $grade)
    {
        [$row, $subject] = $this->gradeRow($grade);
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

        $message = "Grade of {$row->studID} corrected to " . ($value ?? '(no grade)') . '.';
        if ($row->subjComp && $completion === null) {
            $message .= " The completion {$row->subjComp} was removed.";
        }

        return $this->rowResponse($row->id, $subject, $message);
    }

    public function correctCompletion(Request $request, int $grade)
    {
        [$row, $subject] = $this->gradeRow($grade);
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

        return $this->rowResponse($row->id, $subject, "Completion of {$row->studID} corrected to " . ($value ?? '(none)') . '.');
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
