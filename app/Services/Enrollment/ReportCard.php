<?php

namespace App\Services\Enrollment;

use Illuminate\Support\Collection;

use App\Models\EnrollmentDB\Grade;
use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Models\ScheduleDB\EnPrograms;

/**
 * The Student Report Card of one enrollment (one student, term and campus), for StudentReportCardController.
 *
 * Each subject shows its final and completion grade as GPA equivalents (GradeRules::equivalent) and its credits.
 * The average is weighted by credits earned (the old system's rule):
 *   - the grade used is the final grade, or the completion grade when the final is not a passing number (INC, 5.0, ...)
 *   - NSTP subjects (config ciss.report_card.nstp_subjects) and non-numeric grades are left out
 */
class ReportCard
{
    /**
     * @return array{program: ?string, rows: array, average: float}
     */
    public static function of(Student $student, StudEnrolmentHistory $enrollment, string $campus): array
    {
        $subjects = self::subjects($student, $enrollment, $campus);
        $nstp = config('ciss.report_card.nstp_subjects', []);

        $rows = $subjects->map(fn ($subject) => [
            'subject' => $subject->sub_name,
            'title' => $subject->sub_title,
            'grade' => GradeRules::equivalent($subject->subjFgrade, $student->stud_id),
            'completion' => GradeRules::equivalent($subject->subjComp, $student->stud_id),
            'credit' => self::number($subject->creditEarned),
            'nstp' => in_array($subject->subCode, $nstp, true),
            'averaged' => self::averageGrade($subject, $student->stud_id),
        ]);

        return [
            'program' => self::programName($enrollment->progCod),
            'rows' => $rows->all(),
            'average' => self::weightedAverage($rows->reject(fn ($row) => $row['nstp'])),
        ];
    }

    /**
     * The report card average of one student's subjects (each with subjFgrade, subjComp, creditEarned, subCode),
     * e.g. for Enrollment > Ranking. Same rule as of().
     */
    public static function averageOf(Collection $subjects, string $studentId): float
    {
        $nstp = config('ciss.report_card.nstp_subjects', []);

        return self::weightedAverage($subjects
            ->reject(fn ($subject) => in_array($subject->subCode, $nstp, true))
            ->map(fn ($subject) => [
                'credit' => self::number($subject->creditEarned),
                'averaged' => self::averageGrade($subject, $studentId),
            ]));
    }

    // ---------- Helpers ----------

    // The student's subjects of the term in the campus, by subject code
    private static function subjects(Student $student, StudEnrolmentHistory $enrollment, string $campus): Collection
    {
        $schedule = config('database.connections.schedule.database');

        return Grade::join("{$schedule}.sub_offered as offered", 'studgrades.subjID', '=', 'offered.id')
            ->leftJoin("{$schedule}.subjects as subjects", 'offered.subCode', '=', 'subjects.sub_code')
            ->where('studgrades.studID', $student->stud_id)
            ->where('offered.schlyear', $enrollment->schlyear)
            ->where('offered.semester', $enrollment->semester)
            ->whereCampusIn('studgrades.campus', [$campus])
            ->orderBy('offered.subCode')
            ->get([
                'studgrades.subjFgrade', 'studgrades.subjComp', 'studgrades.creditEarned',
                'offered.subCode', 'subjects.sub_name', 'subjects.sub_title',
            ]);
    }

    // The GPA used for the average: the final grade, or the completion grade when the final is not a passing number
    private static function averageGrade($subject, string $studentId): ?float
    {
        $final = $subject->subjFgrade;
        $usesCompletion = (! is_numeric($final) || GradeRules::isFailing($final)) && $subject->subjComp !== null && $subject->subjComp !== '';
        $gpa = GradeRules::equivalent($usesCompletion ? $subject->subjComp : $final, $studentId);

        return is_numeric($gpa) ? (float) $gpa : null;
    }

    // Sum of (GPA x credits) / sum of credits, over the subjects with a numeric grade
    private static function weightedAverage(Collection $rows): float
    {
        $graded = $rows->filter(fn ($row) => $row['averaged'] !== null);
        $credits = $graded->sum('credit');

        return $credits > 0 ? $graded->sum(fn ($row) => $row['averaged'] * $row['credit']) / $credits : 0.0;
    }

    private static function programName(?string $programCode): ?string
    {
        return $programCode ? EnPrograms::where('progCod', $programCode)->value('progName') : null;
    }

    // creditEarned is a text column ("" in many old rows): anything that is not a number counts as 0
    private static function number($value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
