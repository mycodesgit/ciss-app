<?php

namespace App\Services\Enrollment;

use Illuminate\Support\Collection;

use App\Models\EnrollmentDB\Grade;
use App\Models\EnrollmentDB\Student;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * The grades of one student in one campus, grouped by term (oldest first), for Enrollment > View Student Grades.
 * Grades are shown like the Gradesheet: a whole-number percentage becomes its GPA equivalent (GradeRules::equivalent),
 * INC / NN are flagged as warnings, and each term adds up its credits earned.
 */
class StudentGrades
{
    /**
     * @return Collection<int, array{schlyear: string, semester: int, label: string, credits: float, rows: array}>
     */
    public static function of(Student $student, string $campus): Collection
    {
        $schedule = config('database.connections.schedule.database');

        // The term comes from the offered subject (studgrades.schlyear / semester are often empty in old rows)
        $grades = Grade::leftJoin("{$schedule}.sub_offered as offered", 'studgrades.subjID', '=', 'offered.id')
            ->leftJoin("{$schedule}.subjects as subjects", 'offered.subCode', '=', 'subjects.sub_code')
            ->where('studgrades.studID', $student->stud_id)
            ->whereCampusIn('studgrades.campus', [$campus])
            ->selectRaw("
                COALESCE(NULLIF(offered.schlyear, ''), studgrades.schlyear) as schlyear,
                COALESCE(NULLIF(offered.semester, 0), studgrades.semester) as semester,
                studgrades.subjFgrade, studgrades.subjComp, studgrades.creditEarned,
                offered.subSec, offered.subCode, offered.isType, subjects.sub_name, subjects.sub_title
            ")
            ->orderBy('schlyear')
            ->orderBy('semester')
            ->orderBy('subjects.sub_name')
            ->get();

        return $grades
            ->groupBy(fn ($grade) => "{$grade->schlyear}|{$grade->semester}")
            ->map(fn (Collection $termGrades) => self::term($termGrades, $student->stud_id))
            ->values();
    }

    // One term: its label, total credits and grade rows
    private static function term(Collection $grades, string $studentId): array
    {
        $first = $grades->first();
        $term = new ConfigureCurrent(['schlyear' => $first->schlyear, 'semester' => (int) $first->semester]);
        $nstp = config('ciss.report_card.nstp_subjects', []);

        return [
            'schlyear' => $first->schlyear,
            'semester' => (int) $first->semester,
            'label' => $term->label,
            'credits' => $grades->sum(fn ($grade) => self::credit($grade->creditEarned)),
            'rows' => $grades->map(fn ($grade) => [
                'course' => $grade->subSec,
                'subject' => ClassAttendance::subjectLabel($grade),
                'title' => $grade->sub_title,
                'grade' => GradeRules::equivalent($grade->subjFgrade, $studentId),
                'gradeWarning' => GradeRules::isWarning($grade->subjFgrade),
                'completion' => GradeRules::equivalent($grade->subjComp, $studentId),
                'credit' => self::credit($grade->creditEarned),
                // For Student Record: the grade as encoded (e.g. 88) and whether it is NSTP (left out of averages)
                'rawGrade' => $grade->subjFgrade,
                'nstp' => in_array($grade->subCode, $nstp, true),
            ])->values()->all(),
        ];
    }

    // creditEarned is a text column and is "" in many old rows: anything that is not a number counts as 0
    private static function credit($value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
