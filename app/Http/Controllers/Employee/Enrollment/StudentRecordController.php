<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use PDF;

use App\Models\EnrollmentDB\MajorMinor;
use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Services\Enrollment\ClassAttendance;
use App\Services\Enrollment\StudentGrades;

/**
 * Enrollment > Student Record: a student's whole academic record (evaluation sheet).
 *  - index(): the page (search: campus, Student ID)
 *  - find():  AJAX, the student and their grades per term with the Computation (StudentGrades)
 *  - pdf():   the Student Record PDF (src of the result's iframe)
 * Computation (the old system's): per term, the sum of grade x credits over the credits, using the grade as encoded
 * (e.g. 88, not its GPA); grades that are not numbers and NSTP subjects are left out.
 * Campus: administrators can pick any campus, everyone else only their own (FindsEnrollment). Graduate School
 * staff can only view graduate students (Student ID ending in -G or -N). (student-record.js)
 */
class StudentRecordController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    private const GRADUATE_STUDENT_ID = '/-[GN]$/';
    private const NO_MAJOR = ['', '0', '99'];

    public function index()
    {
        return view('pages.employee.enrollment.student-record.index', $this->searchOptions());
    }

    public function find(Request $request)
    {
        [$student, $campus] = $this->student($request);
        $terms = $this->terms($student, $campus);

        return response()->json([
            'student' => [
                'stud_id' => $student->stud_id,
                'name' => ClassAttendance::fullName($student),
                'campus' => str_replace(' Campus', '', config('ciss.campuses')[$campus] ?? $campus),
                'program' => $this->latestEnrollment($student, $campus)['program'],
            ],
            'terms' => $terms,
            'pdf_url' => route('employee.enrollment.student-record.pdf', ['campus' => $campus, 'stud_id' => $student->stud_id]),
        ]);
    }

    public function pdf(Request $request)
    {
        [$student, $campus] = $this->student($request);
        $enrollment = $this->latestEnrollment($student, $campus);

        return PDF::loadView('pdf.student-record', [
            'student' => $student,
            'name' => trim(preg_replace('/\s+/', ' ', $student->fname . ' ' . ($student->mname ? mb_strtoupper(mb_substr($student->mname, 0, 1)) . '.' : '') . ' ' . $student->lname . ' ' . ($student->ext && $student->ext !== 'N/A' ? $student->ext : ''))),
            'address' => collect([$student->brgy, $student->city, $student->province])->filter()->implode(', ') ?: ($student->address ?: null),
            'program' => $enrollment['program'],
            'major' => $enrollment['major'],
            'terms' => $this->terms($student, $campus),
        ])
            ->setPaper('legal', 'portrait')
            ->stream('student-record-' . $student->stud_id . '.pdf');
    }

    // ---------- Helpers ----------

    // The student of the request (campus, stud_id); a validation error (422) with the reason otherwise
    private function student(Request $request): array
    {
        $request->merge(['stud_id' => strtoupper(trim((string) $request->input('stud_id')))]);

        $data = $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'stud_id' => ['required', 'regex:' . $this->studentIdPattern],
        ], ['stud_id.regex' => 'The Student ID Number must look like 2024-0001-K.'], ['stud_id' => 'Student ID Number']);

        $student = Student::where('stud_id', $data['stud_id'])->whereCampusIn('campus', [$data['campus']])->first();

        if (! $student) {
            $this->failEnrollment("Student ID Number {$data['stud_id']} does not exist in this campus.");
        }
        if ($this->isGraduateStaff() && ! preg_match(self::GRADUATE_STUDENT_ID, $student->stud_id)) {
            $this->failEnrollment("Student ID Number {$student->stud_id} is not a Graduate School student.");
        }

        return [$student, $data['campus']];
    }

    // The terms of StudentGrades with the Computation: per row grade x credit, per term credits and average
    private function terms(Student $student, string $campus): Collection
    {
        return StudentGrades::of($student, $campus)->map(function (array $term) {
            $rows = collect($term['rows'])->map(function (array $row) {
                $counted = ! $row['nstp'] && is_numeric($row['rawGrade']) && $row['credit'] > 0;

                return $row + ['counted' => $counted, 'product' => $counted ? round((float) $row['rawGrade'] * $row['credit'], 2) : null];
            });
            $counted = $rows->where('counted', true);
            $credits = (float) $counted->sum('credit');

            return array_merge($term, [
                'rows' => $rows->values()->all(),
                'countedCredits' => $credits,
                'average' => $credits > 0 ? round($counted->sum('product') / $credits, 2) : null,
            ]);
        });
    }

    // Program and major of the student's latest enrollment in the campus
    private function latestEnrollment(Student $student, string $campus): array
    {
        $programs = config('database.connections.schedule.database') . '.programs';

        $enrollment = StudEnrolmentHistory::leftJoin("{$programs} as programs", 'program_en_history.progCod', '=', 'programs.progCod')
            ->where('program_en_history.studentID', $student->stud_id)
            ->whereCampusIn('program_en_history.campus', [$campus])
            ->orderByDesc('program_en_history.id')
            ->first(['programs.progName', 'program_en_history.studMajor']);

        $major = $enrollment && ! in_array((string) $enrollment->studMajor, self::NO_MAJOR, true)
            ? MajorMinor::where('submamiID', $enrollment->studMajor)->value('submamiName')
            : null;

        return ['program' => $enrollment?->progName, 'major' => $major];
    }
}
