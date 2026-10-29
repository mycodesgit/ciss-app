<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;

use Illuminate\Http\Request;

use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Admission\PreEnrollmentForm;

/**
 * Enrollment > Enrollment History: find students of the user's campus by last name or Student ID, then view
 * a student's enrollments (all terms) or print the Pre-Enrollment Form.
 *  - index():         the page (search + results table, history modal, PDF modal)
 *  - search():        AJAX, students matching the search (DataTable)
 *  - history():       AJAX, one student's enrollments in the user's campus
 *  - preEnrollment(): the Pre-Enrollment Form PDF of one student (src of the PDF modal's iframe)
 * Students are always looked up by Student ID and campus (never by database id). (enrollment-history.js)
 */
class EnrollmentHistoryController extends Controller
{
    use ChecksEnrollment;

    private const MIN_SEARCH_LENGTH = 2;
    private const MAX_RESULTS = 500;

    public function index()
    {
        return view('pages.employee.enrollment.history.index');
    }

    // Last name containing the search, or the exact Student ID; the user's campus only
    public function search(Request $request)
    {
        $query = strtoupper(trim((string) $request->input('query')));

        if (mb_strlen($query) < self::MIN_SEARCH_LENGTH) {
            return response()->json(['data' => []]);
        }

        $students = Student::whereIn('campus', $this->campuses())
            ->where(fn ($where) => $where->where('lname', 'like', '%' . addcslashes($query, '%_\\') . '%')->orWhere('stud_id', $query))
            ->orderBy('lname')->orderBy('fname')
            ->limit(self::MAX_RESULTS)
            ->get(['stud_id', 'lname', 'fname', 'mname', 'ext']);

        return response()->json([
            'data' => $students->map(fn (Student $student) => $student->only(['stud_id', 'lname', 'fname', 'mname', 'ext']) + [
                'history_url' => route('employee.enrollment.history.show', $student->stud_id),
                'pdf_url' => route('employee.enrollment.history.pre-enrollment', $student->stud_id),
            ]),
            'limited' => $students->count() === self::MAX_RESULTS,
        ]);
    }

    // The student's enrollments in the user's campus, oldest first
    public function history(string $student)
    {
        $student = $this->studentOfCampus($student);
        $programs = config('database.connections.schedule.database') . '.programs';

        $rows = StudEnrolmentHistory::leftJoin("{$programs} as programs", 'program_en_history.progCod', '=', 'programs.progCod')
            ->where('program_en_history.studentID', $student->stud_id)
            ->whereIn('program_en_history.campus', $this->campuses())
            ->orderBy('program_en_history.schlyear')
            ->orderBy('program_en_history.semester')
            ->get([
                'program_en_history.studentID', 'program_en_history.schlyear', 'program_en_history.semester',
                'program_en_history.studYear', 'program_en_history.studSec', 'program_en_history.campus', 'programs.progAcronym',
            ]);

        return response()->json([
            'name' => trim($student->fname . ' ' . $student->lname),
            'data' => $rows->map(fn ($row) => [
                'studentID' => $row->studentID,
                'schlyear' => $row->schlyear,
                'semester' => (int) $row->semester,
                'semester_name' => ConfigureCurrent::SEMESTERS[(int) $row->semester] ?? (string) $row->semester,
                'course' => $row->progAcronym,
                'studYear' => $row->studYear,
                'studSec' => $row->studSec,
                'campus' => str_replace(' Campus', '', config('ciss.campuses')[$row->campus] ?? $row->campus),
            ]),
        ]);
    }

    public function preEnrollment(string $student)
    {
        $student = $this->studentOfCampus($student);

        return PreEnrollmentForm::forStudent($student)->stream('pre-enrollment-' . $student->stud_id . '.pdf');
    }

    // The student with this Student ID in the user's campus (404 otherwise)
    private function studentOfCampus(string $studentId): Student
    {
        return Student::where('stud_id', strtoupper(trim($studentId)))->whereIn('campus', $this->campuses())->firstOrFail();
    }
}
