<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

use App\Models\EnrollmentDB\DeleteEnrollmentLogs;
use App\Models\EnrollmentDB\StudHisLog;
use App\Models\EnrollmentDB\Student;
use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Enrollment\ClassAttendance;

/**
 * Enrollment > Deleted Enrollment Logs (the old system's "Deleted Enroll. Logs"): the enrollments of a term that were
 * deleted, when and by whom (delete_enrollment_logs, written by Edit Enrollment's Delete).
 *  - index(): the page (search: campus, school year, semester)
 *  - list():  AJAX, the deletions, newest first (DataTable with Excel export)
 * The course is the one of the student's last saved enrollment in the term (studhislog); when there is one, View
 * opens the Enrollment Logs PDF, so what was deleted can still be seen.
 * Registrar and Administrators only ('registrar' middleware, the old menu's roles). (deleted-enrollment-logs.js)
 *
 * students.stud_id has no index, so students are read by id in batches of 500 instead of being joined.
 */
class DeletedEnrollmentLogsController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    private const ID_BATCH = 500;

    public function index()
    {
        return view('pages.employee.enrollment.deleted-enrollment-logs.index', $this->searchOptions());
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

        $deletions = DeleteEnrollmentLogs::where('delschlyear', $data['schlyear'])
            ->where('delsemester', $data['semester'])
            ->whereCampusIn('delMC', [$data['campus']])
            ->orderByDesc('id')
            ->get();

        $studentIds = $deletions->pluck('delstudentID')->unique()->values();
        $students = $studentIds->chunk(self::ID_BATCH)
            ->flatMap(fn (Collection $batch) => Student::whereIn('stud_id', $batch->values()->all())->get(['stud_id', 'lname', 'fname', 'mname', 'ext', 'gender']))
            ->keyBy('stud_id');

        // The course of each student's last saved enrollment in the term
        $courses = $studentIds->chunk(self::ID_BATCH)
            ->flatMap(fn (Collection $batch) => StudHisLog::whereIn('studentID', $batch->values()->all())
                ->where('schlyear', $data['schlyear'])
                ->where('semester', $data['semester'])
                ->whereCampusIn('campus', [$data['campus']])
                ->orderBy('id')
                ->get(['studentID', 'course']))
            ->toBase()
            ->mapWithKeys(fn ($log) => [$log->studentID => $log->course]);

        return response()->json([
            'data' => $deletions->map(function ($deletion) use ($students, $courses, $data) {
                $student = $students[$deletion->delstudentID] ?? null;
                $logged = $courses->has($deletion->delstudentID);

                return [
                    'deleted' => $deletion->created_at?->format('M d, Y h:i A'),
                    'deleted_sort' => $deletion->created_at?->format('Y-m-d H:i:s'),
                    'studentID' => $deletion->delstudentID,
                    'name' => $student ? ClassAttendance::fullName($student) : '',
                    'gender' => $student?->gender,
                    'course' => $courses[$deletion->delstudentID] ?? null,
                    'deletedBy' => $deletion->delemployeename,
                    'logs_url' => $logged ? route('employee.enrollment.enrollment-logs.pdf', $data + ['stud_id' => $deletion->delstudentID]) : null,
                ];
            })->values(),
        ]);
    }
}
