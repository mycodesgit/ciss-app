<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\SearchesCourses;

use Illuminate\Http\Request;

use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Services\Enrollment\ClassAttendance;
use App\Services\Enrollment\GradeRules;

/**
 * Enrollment > EL and PL (Enrollment List and Promotional List): the students of one program in a term,
 * one row per subject with its grade and units.
 *  - index():   the page (search: campus, school year, semester, course)
 *  - courses(): AJAX, the programs with classes in the campus and term (Course select, SearchesCourses)
 *  - list():    AJAX, the students' subjects and grades (DataTable)
 * Grades are shown like the Gradesheet (GradeRules::equivalent). Graduate School staff see graduate school programs only.
 * Campus: administrators can pick any campus, everyone else only their own (FindsEnrollment). (el-pl.js)
 */
class ElPlController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, SearchesCourses;

    public function index()
    {
        return view('pages.employee.enrollment.el-pl.index', $this->searchOptions());
    }

    // Empty until a course is searched (the table loads once when the page opens)
    public function list(Request $request)
    {
        if (! $request->filled('progCod') || ! ($data = $this->validateCourse($request))) {
            return response()->json(['data' => []]);
        }

        $schedule = config('database.connections.schedule.database');

        // The term's subjects come from the offered subject (studgrades.schlyear / semester are often empty in old rows)
        $rows = StudEnrolmentHistory::join('students', 'program_en_history.studentID', '=', 'students.stud_id')
            ->join('studgrades', 'program_en_history.studentID', '=', 'studgrades.studID')
            ->join("{$schedule}.sub_offered as offered", 'studgrades.subjID', '=', 'offered.id')
            ->leftJoin("{$schedule}.subjects as subjects", 'offered.subCode', '=', 'subjects.sub_code')
            ->where('program_en_history.schlyear', $data['schlyear'])
            ->where('program_en_history.semester', $data['semester'])
            ->whereCampusIn('program_en_history.campus', [$data['campus']])
            ->where('program_en_history.progCod', $data['progCod'])
            ->where('offered.schlyear', $data['schlyear'])
            ->where('offered.semester', $data['semester'])
            ->orderBy('program_en_history.studYear')
            ->orderBy('students.lname')
            ->orderBy('students.fname')
            ->orderBy('subjects.sub_name')
            ->get([
                'program_en_history.studentID', 'program_en_history.studYear',
                'students.lname', 'students.fname', 'students.mname', 'students.ext', 'students.gender', 'students.address',
                'studgrades.id as grade_id', 'studgrades.subjFgrade',
                'offered.subSec', 'offered.subUnit', 'offered.isType', 'subjects.sub_name',
            ]);

        return response()->json([
            'data' => $rows->map(fn ($row) => [
                'id' => $row->grade_id,
                'studentID' => $row->studentID,
                'lname' => $row->lname,
                'fname' => $row->fname,
                'mname' => $row->mname,
                'ext' => $row->ext && $row->ext !== 'N/A' ? $row->ext : '',
                'gender' => $row->gender,
                'address' => $row->address,
                'degree' => $row->subSec,
                'studYear' => $row->studYear,
                'subject' => ClassAttendance::subjectLabel($row),
                'grade' => GradeRules::equivalent($row->subjFgrade, $row->studentID),
                'units' => $row->subUnit,
            ]),
        ]);
    }
}
