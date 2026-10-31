<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\EnrollmentDB\Grade;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Enrollment > CWTS/LTS/ROTC: how many students take each NSTP component in a campus and term.
 *  - index(): the page (search: campus, school year, semester)
 *  - count(): AJAX, one row per component (male / female / total) and the overall total
 * A student counts when they have a grade row (studgrades) in one of the component's offered subjects
 * (config ciss.nstp_components). Access: the NSTP menu in User Access ('menu.access:nstp-url'). Campus: administrators can pick any campus, everyone else only their own
 * (FindsEnrollment). (nstp-enrollees.js)
 */
class NstpEnrolleesController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    public function index()
    {
        return view('pages.employee.enrollment.nstp-enrollees.index', $this->searchOptions());
    }

    public function count(Request $request)
    {
        $data = $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'schlyear' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ]);

        $components = config('ciss.nstp_components', []);
        $schedule = config('database.connections.schedule.database');

        // Left join: a grade row still counts when its student record is missing (gender then unknown)
        $counts = Grade::join("{$schedule}.sub_offered as offered", 'studgrades.subjID', '=', 'offered.id')
            ->leftJoin('students', 'studgrades.studID', '=', 'students.stud_id')
            ->where('offered.schlyear', $data['schlyear'])
            ->where('offered.semester', $data['semester'])
            ->whereCampusIn('offered.campus', [$data['campus']])
            ->whereIn('offered.subCode', collect($components)->flatten()->all())
            ->groupBy('offered.subCode')
            ->selectRaw("
                offered.subCode,
                COUNT(DISTINCT studgrades.studID) as total,
                COUNT(DISTINCT CASE WHEN UPPER(students.gender) = 'MALE' THEN studgrades.studID END) as male,
                COUNT(DISTINCT CASE WHEN UPPER(students.gender) = 'FEMALE' THEN studgrades.studID END) as female
            ")
            ->get()
            ->toBase() // a plain collection: Eloquent's only() would match model ids, not these keys
            ->keyBy('subCode');

        // A student takes one subject per component in a term, so the subjects' counts add up
        $rows = collect($components)->map(function (array $codes, string $component) use ($counts) {
            $subjects = $counts->only($codes);

            return [
                'component' => $component,
                'total' => (int) $subjects->sum('total'),
                'male' => (int) $subjects->sum('male'),
                'female' => (int) $subjects->sum('female'),
            ];
        })->values();

        $term = new ConfigureCurrent(['schlyear' => $data['schlyear'], 'semester' => (int) $data['semester']]);

        return response()->json([
            'term' => $term->label,
            'campus' => str_replace(' Campus', '', config('ciss.campuses')[$data['campus']] ?? $data['campus']),
            'components' => $rows,
            'total' => $rows->sum('total'),
            'male' => $rows->sum('male'),
            'female' => $rows->sum('female'),
        ]);
    }
}
