<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PDF;

use App\Models\ScheduleDB\SubjectOffered;
use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Enrollment\ClassAttendance;

/**
 * Enrollment > Class Attendance: the subjects offered in a term, and a printable attendance sheet per subject.
 *  - index():    the page (search: campus, school year, semester)
 *  - list():     AJAX, one row per offered subject with the number of students (male / female)
 *  - students(): AJAX, the students of one subject (modal)
 *  - pdf():      the attendance sheet of one subject (src of the modal's iframe)
 * Queries: App\Services\Enrollment\ClassAttendance. Campus: administrators can pick any campus,
 * everyone else only their own (FindsEnrollment). (class-attendance.js)
 */
class ClassAttendanceController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    public function index()
    {
        return view('pages.employee.enrollment.class-attendance.index', $this->searchOptions());
    }

    // Empty until a term is searched (the table loads once when the page opens)
    public function list(Request $request)
    {
        if (! $request->filled('semester')) {
            return response()->json(['data' => []]);
        }

        $data = $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'schlyear' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ]);

        $subjects = $this->attendance()->subjects($data['campus'], $data['schlyear'], $data['semester']);

        return response()->json([
            'data' => $subjects->map(fn ($subject) => [
                'id' => $subject->id,
                'subject' => ClassAttendance::subjectLabel($subject),
                'title' => $subject->sub_title,
                'section' => $subject->subSec,
                'total' => $subject->total,
                'male' => $subject->male,
                'female' => $subject->female,
                'students_url' => route('employee.enrollment.class-attendance.students', $subject->id),
                'pdf_url' => route('employee.enrollment.class-attendance.pdf', $subject->id),
            ]),
        ]);
    }

    public function students(int $subject)
    {
        $students = $this->attendance()->students($this->findSubject($subject));

        return response()->json([
            'data' => $students->map(fn ($student) => [
                'studentID' => $student->stud_id,
                'name' => $student->full_name,
                'gender' => $student->gender,
            ]),
        ]);
    }

    public function pdf(int $subject)
    {
        $subject = $this->findSubject($subject);

        return PDF::loadView('pdf.class-attendance', [
            'subject' => $subject,
            'subjectLabel' => ClassAttendance::subjectLabel($subject),
            'term' => new ConfigureCurrent(['schlyear' => $subject->schlyear, 'semester' => (int) $subject->semester]),
            'students' => $this->attendance()->students($subject),
        ])
            ->setPaper('legal', 'portrait')
            ->stream('class-attendance.pdf');
    }

    // ---------- Helpers ----------

    private function attendance(): ClassAttendance
    {
        return new ClassAttendance($this->isGraduateStaff());
    }

    // The subject, only when it is offered in a campus the user may search (otherwise 404)
    private function findSubject(int $id): SubjectOffered
    {
        return $this->attendance()->subject($id, array_keys($this->searchableCampuses())) ?? abort(404);
    }
}
