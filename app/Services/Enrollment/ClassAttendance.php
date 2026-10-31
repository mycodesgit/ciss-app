<?php

namespace App\Services\Enrollment;

use Illuminate\Support\Collection;

use App\Models\EnrollmentDB\Grade;
use App\Models\EnrollmentDB\Student;
use App\Models\ScheduleDB\SubjectOffered;

/**
 * The data of Enrollment > Class Attendance (used by ClassAttendanceController):
 *  - subjects(): the subjects offered in a term and campus, each with its number of students (male / female)
 *  - subject():  one offered subject, only when it is in one of the given campuses
 *  - students(): the students of a subject (studgrades), sorted by name
 *
 * Graduate School staff work with graduate school subjects only (subCode like "%-GSS-%"); everyone else
 * with all the other subjects (the old system's rule).
 */
class ClassAttendance
{
    private const GRADUATE_SUBJECTS = '%-GSS-%';

    public function __construct(private bool $graduateStaff)
    {
    }

    /**
     * @return Collection<int, object>  id, subCode, subSec, isType, sub_name, sub_title, total, male, female
     */
    public function subjects(string $campus, string $schlyear, string $semester): Collection
    {
        $subjects = $this->offered()
            ->where('sub_offered.schlyear', $schlyear)
            ->where('sub_offered.semester', $semester)
            ->whereCampusIn('sub_offered.campus', [$campus])
            ->orderBy('subjects.sub_name')
            ->orderBy('sub_offered.subSec')
            ->get();

        $counts = $this->studentCounts($subjects->pluck('id')->all());

        return $subjects->each(function ($subject) use ($counts) {
            $count = $counts->get($subject->id);
            $subject->total = (int) ($count->total ?? 0);
            $subject->male = (int) ($count->male ?? 0);
            $subject->female = (int) ($count->female ?? 0);
        });
    }

    public function subject(int $id, array $campuses): ?SubjectOffered
    {
        return $this->offered()
            ->where('sub_offered.id', $id)
            ->whereCampusIn('sub_offered.campus', $campuses)
            ->first();
    }

    /**
     * @return Collection<int, object>  stud_id, full_name, gender
     */
    public function students(SubjectOffered $subject): Collection
    {
        $studentIds = Grade::where('subjID', $subject->id)->distinct()->pluck('studID');
        $records = $this->studentRecords($studentIds->all(), ['stud_id', 'lname', 'fname', 'mname', 'ext', 'gender']);

        // A grade whose student record is missing is still listed (by Student ID) so the count matches
        return $studentIds
            ->map(fn ($id) => (object) [
                'stud_id' => $id,
                'full_name' => isset($records[$id]) ? self::fullName($records[$id]) : '',
                'gender' => $records[$id]->gender ?? '',
                'sort' => isset($records[$id]) ? "{$records[$id]->lname} {$records[$id]->fname}" : "~{$id}", // missing names last
            ])
            ->sortBy('sort', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    // "BIO 101 - Lab" (the old system shows isType unless it is "No")
    public static function subjectLabel($subject): string
    {
        return $subject->isType && $subject->isType !== 'No' ? "{$subject->sub_name} - {$subject->isType}" : (string) $subject->sub_name;
    }

    // "DELA CRUZ, JUAN A. JR."
    public static function fullName($student): string
    {
        $middle = $student->mname ? mb_substr($student->mname, 0, 1) . '.' : '';
        $ext = $student->ext && $student->ext !== 'N/A' ? $student->ext : '';

        return trim(preg_replace('/\s+/', ' ', "{$student->lname}, {$student->fname} {$middle} {$ext}"));
    }

    // ---------- Helpers ----------

    // Offered subjects with their name and title, limited to the user's kind of subjects
    private function offered()
    {
        return SubjectOffered::join('subjects', 'sub_offered.subCode', '=', 'subjects.sub_code')
            ->where('sub_offered.subCode', $this->graduateStaff ? 'like' : 'not like', self::GRADUATE_SUBJECTS)
            ->select([
                'sub_offered.id', 'sub_offered.subCode', 'sub_offered.subSec', 'sub_offered.isType',
                'sub_offered.schlyear', 'sub_offered.semester', 'sub_offered.campus',
                'subjects.sub_name', 'subjects.sub_title',
            ]);
    }

    /**
     * Students per subject id: total, male, female.
     * Two simple queries instead of a studgrades-students join: students.stud_id has no index,
     * so the join is slow on a whole term, while "WHERE stud_id IN (...)" reads the table once.
     */
    private function studentCounts(array $subjectIds): Collection
    {
        if (! $subjectIds) {
            return collect();
        }

        $grades = Grade::whereIn('subjID', $subjectIds)->distinct()->get(['subjID', 'studID']);
        $genders = $this->studentRecords($grades->pluck('studID')->unique()->all(), ['stud_id', 'gender'])
            ->map(fn ($student) => strtoupper((string) $student->gender));

        return $grades->groupBy('subjID')->map(fn ($subjectGrades) => (object) [
            'total' => $subjectGrades->count(),
            'male' => $subjectGrades->filter(fn ($grade) => $genders->get($grade->studID) === 'MALE')->count(),
            'female' => $subjectGrades->filter(fn ($grade) => $genders->get($grade->studID) === 'FEMALE')->count(),
        ]);
    }

    // Student records by stud_id, in chunks of 500: with more than 1000 values MariaDB turns the IN list into a
    // join (in_predicate_conversion_threshold) that finds no students
    private function studentRecords(array $studentIds, array $columns): Collection
    {
        return collect($studentIds)
            ->chunk(500)
            ->flatMap(fn ($ids) => Student::whereIn('stud_id', $ids->values()->all())->get($columns))
            ->keyBy('stud_id');
    }
}
