<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Enrollment > Number of Enrollees: how many students are enrolled in a campus and term, per year level.
 *  - index(): the page (search: campus, school year, semester)
 *  - count(): AJAX, the total and one row per year level (male / female / total)
 * Enrolled = program_en_history.status 2 or 3; graduate school students (Student ID with -G) are not counted
 * (the old system's rule). Hidden from Encoders like the other reports ('enrollment-reports' middleware).
 * Campus: administrators can pick any campus, everyone else only their own (FindsEnrollment). (number-of-enrollees.js)
 */
class NumberOfEnrolleesController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    private const ENROLLED_STATUSES = [2, 3];
    private const GRADUATE_STUDENT_ID = '%-G%';

    public function index()
    {
        return view('pages.employee.enrollment.number-of-enrollees.index', $this->searchOptions());
    }

    public function count(Request $request)
    {
        $data = $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'schlyear' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ]);

        // Left join: an enrollment still counts when its student record is missing (gender then unknown)
        $years = StudEnrolmentHistory::leftJoin('students','program_en_history.studentID', '=', 'students.stud_id')
            ->where('program_en_history.schlyear', $data['schlyear'])
            ->where('program_en_history.semester', $data['semester'])
            ->whereCampusIn('program_en_history.campus', [$data['campus']])
            ->whereIn('program_en_history.status', self::ENROLLED_STATUSES)
            ->where('program_en_history.studentID', 'not like', self::GRADUATE_STUDENT_ID)
            ->groupBy('program_en_history.studYear')
            ->orderBy('program_en_history.studYear')
            ->selectRaw("
                program_en_history.studYear,
                COUNT(DISTINCT program_en_history.studentID) as total,
                COUNT(DISTINCT CASE WHEN UPPER(students.gender) = 'MALE' THEN program_en_history.studentID END) as male,
                COUNT(DISTINCT CASE WHEN UPPER(students.gender) = 'FEMALE' THEN program_en_history.studentID END) as female
            ")
            ->get()
            ->map(fn ($year) => [
                'year' => $year->studYear,
                'total' => (int) $year->total,
                'male' => (int) $year->male,
                'female' => (int) $year->female,
            ]);

        $term = new ConfigureCurrent(['schlyear' => $data['schlyear'], 'semester' => (int) $data['semester']]);

        return response()->json([
            'term' => $term->label,
            'campus' => str_replace(' Campus', '', config('ciss.campuses')[$data['campus']] ?? $data['campus']),
            'years' => $years,
            'total' => $years->sum('total'),
            'male' => $years->sum('male'),
            'female' => $years->sum('female'),
        ]);
    }
}
