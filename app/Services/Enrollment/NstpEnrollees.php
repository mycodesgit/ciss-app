<?php

namespace App\Services\Enrollment;

use Illuminate\Support\Collection;

use App\Models\EnrollmentDB\Grade;
use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Models\ScheduleDB\SubjectOffered;

/**
 * Students taking NSTP subjects (config ciss.nstp_components), for the NSTP module and Enrollment > CWTS/LTS/ROTC.
 * A student counts when they have a grade row (studgrades) in one of the component's subjects offered in the
 * campus and term; a grade row still counts when its student record is missing (gender then unknown).
 *
 * Speed: students.stud_id has no index, so students are read in one query by Student ID instead of being joined
 * to every grade row, and the offered subjects (a few rows) are looked up before studgrades.
 */
class NstpEnrollees
{
    private const ID_BATCH = 500;

    // ['cwts' => ['label', 'name', 'codes'], ...]
    public static function components(): array
    {
        return config('ciss.nstp_components', []);
    }

    /**
     * One row per component with male / female / total students.
     * @return Collection<int, array{key: string, component: string, name: string, total: int, male: int, female: int}>
     */
    public static function counts(array $campuses, string $schlyear, $semester): Collection
    {
        $components = self::components();
        $grades = self::grades($campuses, $schlyear, $semester, collect($components)->pluck('codes')->flatten()->all())
            ->get(['studgrades.studID', 'offered.subCode']);
        $genders = self::studentsOf($grades, ['stud_id', 'gender'])->map(fn ($student) => strtoupper((string) $student->gender));

        return collect($components)->map(function (array $component, string $key) use ($grades, $genders) {
            $studentIds = $grades->whereIn('subCode', $component['codes'])->pluck('studID')->unique();

            return [
                'key' => $key,
                'component' => $component['label'],
                'name' => $component['name'],
                'total' => $studentIds->count(),
                'male' => $studentIds->filter(fn ($id) => ($genders[$id] ?? null) === 'MALE')->count(),
                'female' => $studentIds->filter(fn ($id) => ($genders[$id] ?? null) === 'FEMALE')->count(),
            ];
        })->values();
    }

    // The students of one component, with what the CHED list needs (program = their enrollment of the term)
    public static function students(string $component, array $campuses, string $schlyear, $semester): Collection
    {
        $schedule = config('database.connections.schedule.database');

        $grades = self::grades($campuses, $schlyear, $semester, self::components()[$component]['codes'])
            ->leftJoin("{$schedule}.subjects as subjects", 'offered.subCode', '=', 'subjects.sub_code')
            ->get(['studgrades.id', 'studgrades.studID', 'offered.schlyear', 'offered.subSec', 'subjects.sub_name']);

        $students = self::studentsOf($grades, [
            'stud_id', 'lname', 'fname', 'mname', 'ext', 'bday', 'gender', 'region', 'province', 'city', 'brgy', 'email', 'contact',
        ]);

        // The program of each student's enrollment in the term (a student can have more than one enrollment row)
        $programs = $grades->pluck('studID')->unique()->chunk(self::ID_BATCH)
            ->flatMap(fn (Collection $studentIds) => StudEnrolmentHistory::leftJoin("{$schedule}.programs as programs", 'program_en_history.progCod', '=', 'programs.progCod')
                ->whereIn('program_en_history.studentID', $studentIds->values()->all())
                ->where('program_en_history.schlyear', $schlyear)
                ->where('program_en_history.semester', $semester)
                ->whereCampusIn('program_en_history.campus', $campuses)
                ->pluck('programs.progName', 'program_en_history.studentID'));

        return $grades
            ->each(function ($grade) use ($students, $programs) {
                $grade->student = $students[$grade->studID] ?? null;
                $grade->progName = $programs[$grade->studID] ?? null;
            })
            ->sortBy(fn ($grade) => [$grade->student->lname ?? "\u{FFFF}", $grade->student->fname ?? '', $grade->studID])
            ->values();
    }

    // Grade rows in the given subjects offered in the campus and term (the offered subjects are looked up first)
    private static function grades(array $campuses, string $schlyear, $semester, array $codes)
    {
        $schedule = config('database.connections.schedule.database');

        $offeredIds = SubjectOffered::where('schlyear', $schlyear)
            ->where('semester', $semester)
            ->whereCampusIn('campus', $campuses)
            ->whereIn('subCode', $codes)
            ->pluck('id')
            ->all();

        return Grade::join("{$schedule}.sub_offered as offered", 'studgrades.subjID', '=', 'offered.id')
            ->whereIn('studgrades.subjID', $offeredIds ?: [0]);
    }

    // The student records of the grade rows, by Student ID. In batches: with more than 1000 values MariaDB turns
    // the IN list into a join (in_predicate_conversion_threshold) that finds no students here.
    private static function studentsOf(Collection $grades, array $columns): Collection
    {
        return $grades->pluck('studID')->unique()->chunk(self::ID_BATCH)
            ->flatMap(fn (Collection $studentIds) => Student::whereIn('stud_id', $studentIds->values()->all())->get($columns))
            ->keyBy('stud_id');
    }
}
