<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\SearchesCourses;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use App\Models\EnrollmentDB\Grade;
use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Services\Enrollment\ClassAttendance;
use App\Services\Enrollment\ReportCard;

/**
 * Enrollment > Ranking: the students of one program in a term, ranked by their GWA (best = lowest first).
 *  - index():   the page (search: campus, school year, semester, course)
 *  - courses(): AJAX, the programs with classes in the campus and term (Course select, SearchesCourses)
 *  - list():    AJAX, one row per student with rank, GWA and graded subjects (DataTable)
 * The GWA is the Student Report Card average (ReportCard::averageOf). Students with no numeric grade yet are
 * listed last without a rank; equal GWAs share a rank (1, 2, 2, 4).
 * Campus: administrators can pick any campus, everyone else only their own (FindsEnrollment). (ranking.js)
 */
class RankingController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, SearchesCourses;

    private const GWA_DECIMALS = 4;
    private const ID_BATCH = 500;

    public function index()
    {
        return view('pages.employee.enrollment.ranking.index', $this->searchOptions());
    }

    // Empty until a course is searched (the table loads once when the page opens)
    public function list(Request $request)
    {
        if (! $request->filled('progCod') || ! ($data = $this->validateCourse($request))) {
            return response()->json(['data' => []]);
        }

        $students = StudEnrolmentHistory::join('students', 'program_en_history.studentID', '=', 'students.stud_id')
            ->where('program_en_history.schlyear', $data['schlyear'])
            ->where('program_en_history.semester', $data['semester'])
            ->whereCampusIn('program_en_history.campus', [$data['campus']])
            ->where('program_en_history.progCod', $data['progCod'])
            ->get([
                'program_en_history.studentID', 'program_en_history.studYear', 'program_en_history.studSec',
                'students.lname', 'students.fname', 'students.mname', 'students.ext',
            ])
            ->unique('studentID');

        $grades = $this->grades($students->pluck('studentID')->all(), $data);

        $rows = $students->map(function ($student) use ($grades) {
            $subjects = $grades->get($student->studentID, collect());
            $graded = $subjects->filter(fn ($subject) => is_numeric($subject->subjFgrade) || is_numeric($subject->subjComp));

            return [
                'id' => $student->studentID,
                'studentID' => $student->studentID,
                'name' => ClassAttendance::fullName($student),
                'section' => "{$student->studYear}-{$student->studSec}",
                'subjects' => $subjects->count(),
                'graded' => $graded->count(),
                'gwa' => $graded->isEmpty() ? null : round(ReportCard::averageOf($subjects, $student->studentID), self::GWA_DECIMALS),
            ];
        });

        return response()->json(['data' => $this->ranked($rows)]);
    }

    // ---------- Helpers ----------

    // The term's grades of the students in the campus, by Student ID. In batches: with more than 1000 values
    // MariaDB turns the IN list into a join (in_predicate_conversion_threshold) that finds nothing here.
    private function grades(array $studentIds, array $data): Collection
    {
        $schedule = config('database.connections.schedule.database');

        return collect($studentIds)->chunk(self::ID_BATCH)
            ->flatMap(fn (Collection $batch) => Grade::join("{$schedule}.sub_offered as offered", 'studgrades.subjID', '=', 'offered.id')
                ->whereIn('studgrades.studID', $batch->values()->all())
                ->where('offered.schlyear', $data['schlyear'])
                ->where('offered.semester', $data['semester'])
                ->whereCampusIn('studgrades.campus', [$data['campus']])
                ->get(['studgrades.studID', 'studgrades.subjFgrade', 'studgrades.subjComp', 'studgrades.creditEarned', 'offered.subCode']))
            ->groupBy('studID');
    }

    // Best GWA first, then by name; equal GWAs share a rank; no GWA = no rank (last)
    private function ranked(Collection $rows): array
    {
        $sorted = $rows->sortBy([
            fn ($a, $b) => ($a['gwa'] === null) <=> ($b['gwa'] === null),
            fn ($a, $b) => $a['gwa'] <=> $b['gwa'],
            fn ($a, $b) => $a['name'] <=> $b['name'],
        ])->values();

        $rank = 0;
        $previous = null;

        return $sorted->map(function ($row, $index) use (&$rank, &$previous) {
            if ($row['gwa'] !== null && $row['gwa'] !== $previous) {
                $rank = $index + 1;
                $previous = $row['gwa'];
            }

            return ['rank' => $row['gwa'] === null ? null : $rank] + $row;
        })->all();
    }
}
