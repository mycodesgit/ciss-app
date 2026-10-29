<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PDF;

use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Enrollment > Student Per Degree: the enrolled students of a term per program, year and section.
 *  - index():    the page (search: campus, school year, semester)
 *  - list():     AJAX, one row per program + year + section with the number of students (male / female)
 *  - students(): AJAX, the students of one program + year + section (modal)
 *  - pdf():      the same students as a PDF, males then females (src of the modal's iframe)
 * Enrolled = program_en_history.status 2 or 3. Graduate School staff see graduate school programs only.
 * Campus: administrators can pick any campus, everyone else only their own (FindsEnrollment). (student-per-degree.js)
 */
class StudentPerDegreeController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    private const ENROLLED_STATUSES = [2, 3];
    private const GRADUATE_PROGRAMS = '%-GSS-%'; // progCod of graduate school programs

    public function index()
    {
        return view('pages.employee.enrollment.per-degree.index', $this->searchOptions());
    }

    // Empty until a term is searched (the table loads once when the page opens)
    public function list(Request $request)
    {
        if (! $request->filled('semester')) {
            return response()->json(['data' => []]);
        }

        $data = $this->validateTerm($request);
        $programs = $this->programsTable();

        $rows = $this->enrolled($data)
            ->leftJoin("{$programs} as programs", 'program_en_history.progCod', '=', 'programs.progCod')
            ->groupBy('program_en_history.progCod', 'program_en_history.studYear', 'program_en_history.studSec', 'programs.progName', 'programs.progAcronym')
            ->orderBy('programs.progAcronym')->orderBy('program_en_history.studYear')->orderBy('program_en_history.studSec')
            ->selectRaw("
                program_en_history.progCod, program_en_history.studYear, program_en_history.studSec,
                programs.progName, programs.progAcronym,
                COUNT(DISTINCT students.stud_id) as total,
                COUNT(DISTINCT CASE WHEN UPPER(students.gender) = 'MALE' THEN students.stud_id END) as male,
                COUNT(DISTINCT CASE WHEN UPPER(students.gender) = 'FEMALE' THEN students.stud_id END) as female
            ")
            ->get();

        return response()->json([
            'data' => $rows->map(function ($row) use ($data) {
                $class = $data + ['progCod' => $row->progCod, 'studYear' => $row->studYear, 'studSec' => $row->studSec];

                return [
                    'id' => "{$row->progCod}|{$row->studYear}|{$row->studSec}",
                    'progCod' => $row->progCod,
                    'progName' => $row->progName,
                    'progAcronym' => $row->progAcronym,
                    'section' => "{$row->studYear}-{$row->studSec}",
                    'total' => (int) $row->total,
                    'male' => (int) $row->male,
                    'female' => (int) $row->female,
                    'students_url' => route('employee.enrollment.per-degree.students', $class),
                    'pdf_url' => route('employee.enrollment.per-degree.pdf', $class),
                ];
            }),
        ]);
    }

    public function students(Request $request)
    {
        $students = $this->classStudents($this->validateClass($request));

        return response()->json([
            'data' => $students->map(fn ($student) => [
                'studentID' => $student->studentID,
                'name' => $student->full_name,
                'gender' => $student->gender,
            ]),
        ]);
    }

    public function pdf(Request $request)
    {
        $data = $this->validateClass($request);
        $students = $this->classStudents($data);
        $first = $students->first();
        $term = new ConfigureCurrent(['schlyear' => $data['schlyear'], 'semester' => (int) $data['semester']]);

        return PDF::loadView('pdf.student-per-degree', [
            'title' => trim(($first->progName ?? $data['progCod']) . " {$data['studYear']}-{$data['studSec']}"),
            'term' => $term,
            'campusName' => config('ciss.campuses')[$data['campus']] ?? $data['campus'],
            'groups' => [
                'Male Students' => $students->filter(fn ($student) => strtoupper((string) $student->gender) === 'MALE'),
                'Female Students' => $students->filter(fn ($student) => strtoupper((string) $student->gender) === 'FEMALE'),
                'Other / Unknown Gender' => $students->reject(fn ($student) => in_array(strtoupper((string) $student->gender), ['MALE', 'FEMALE'], true)),
            ],
            'total' => $students->count(),
        ])
            ->setPaper('legal', 'portrait')
            ->stream('student-per-degree.pdf');
    }

    // ---------- Helpers ----------

    private function validateTerm(Request $request): array
    {
        return $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'schlyear' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ]);
    }

    private function validateClass(Request $request): array
    {
        return $this->validateTerm($request) + $request->validate([
            'progCod' => ['required', 'string', 'max:50'],
            'studYear' => ['required', 'string', 'max:10'],
            'studSec' => ['required', 'string', 'max:10'],
        ]);
    }

    // Enrollments of the term in the campus, with the student (Graduate School staff: graduate programs only)
    private function enrolled(array $data)
    {
        return StudEnrolmentHistory::join('students', 'program_en_history.studentID', '=', 'students.stud_id')
            ->where('program_en_history.schlyear', $data['schlyear'])
            ->where('program_en_history.semester', $data['semester'])
            ->whereCampusIn('program_en_history.campus', [$data['campus']])
            ->whereIn('program_en_history.status', self::ENROLLED_STATUSES)
            ->when($this->isGraduateStaff(), fn ($query) => $query->where('program_en_history.progCod', 'like', self::GRADUATE_PROGRAMS));
    }

    // The students of one program + year + section (each with full_name)
    private function classStudents(array $data)
    {
        return $this->enrolled($data)
            ->leftJoin("{$this->programsTable()} as programs", 'program_en_history.progCod', '=', 'programs.progCod')
            ->where('program_en_history.progCod', $data['progCod'])
            ->where('program_en_history.studYear', $data['studYear'])
            ->where('program_en_history.studSec', $data['studSec'])
            ->orderBy('students.lname')->orderBy('students.fname')
            ->get(['program_en_history.studentID', 'students.lname', 'students.fname', 'students.mname', 'students.ext', 'students.gender', 'programs.progName'])
            ->unique('studentID')
            ->each(fn ($student) => $student->full_name = $this->fullName($student))
            ->values();
    }

    private function programsTable(): string
    {
        return config('database.connections.schedule.database') . '.programs';
    }

    private function fullName($student): string
    {
        $middle = $student->mname ? mb_substr($student->mname, 0, 1) . '.' : '';
        $ext = $student->ext && $student->ext !== 'N/A' ? $student->ext : '';

        return trim(preg_replace('/\s+/', ' ', "{$student->lname}, {$student->fname} {$middle} {$ext}"));
    }
}
