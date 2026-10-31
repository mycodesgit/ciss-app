<?php

namespace App\Http\Controllers\Employee\Nstp;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Enrollment\NstpEnrollees;

/**
 * NSTP > CWTS / LTS / ROTC / NSTP 1/2 Students: the students of one component in a campus and term,
 * in the CHED NSTP serial number list format (22 columns, exported to Excel).
 *  - index(): the page (search: campus, school year, semester); {component} = a key of config ciss.nstp_components
 *  - list():  AJAX, the students (NstpEnrollees::students)
 * Campus: administrators can pick any campus, everyone else only their own (FindsEnrollment). (nstp/students.js)
 */
class NstpStudentsController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    public function index(string $component)
    {
        return view('pages.employee.nstp.students.index', $this->searchOptions() + [
            'component' => NstpEnrollees::components()[$component] + ['key' => $component],
            'hei' => config('ciss.nstp_hei'),
        ]);
    }

    // Empty until a term is searched (the table loads once when the page opens)
    public function list(Request $request, string $component)
    {
        if (! $request->filled('semester')) {
            return response()->json(['data' => []]);
        }

        $data = $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'schlyear' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ]);

        $students = NstpEnrollees::students($component, [$data['campus']], $data['schlyear'], $data['semester']);

        return response()->json([
            // $row->student is null when the student record is missing (only the Student ID is shown then)
            'data' => $students->map(fn ($row) => [
                'id' => $row->id,
                'studID' => $row->studID,
                'schlyear' => $row->schlyear,
                'program' => $row->sub_name,
                'section' => $row->subSec,
                'progName' => $row->progName,
            ] + collect(['region', 'lname', 'fname', 'mname', 'bday', 'gender', 'brgy', 'city', 'province', 'email', 'contact'])
                ->mapWithKeys(fn ($column) => [$column => $row->student?->{$column}])->all() + [
                'ext' => $row->student?->ext && $row->student->ext !== 'N/A' ? $row->student->ext : '',
            ]),
        ]);
    }
}
