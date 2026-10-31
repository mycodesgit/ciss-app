<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Enrollment\NstpEnrollees;

/**
 * Enrollment > CWTS/LTS/ROTC: how many students take each NSTP component in a campus and term.
 *  - index(): the page (search: campus, school year, semester)
 *  - count(): AJAX, one row per component (male / female / total) and the overall total (NstpEnrollees::counts)
 * Access: the NSTP menu in User Access ('menu.access:nstp-url'). Campus: administrators can pick any campus,
 * everyone else only their own (FindsEnrollment). (nstp-enrollees.js)
 * The NSTP module shows the same page in its own layout: Nstp\NstpEnrolleesController sets the properties below.
 */
class NstpEnrolleesController extends Controller
{
    use ChecksEnrollment, FindsEnrollment;

    protected string $routeName = 'employee.enrollment.nstp-enrollees';
    protected array $module = ['name' => 'Enrollment', 'layout' => 'layouts.master_empenrollment'];

    public function index()
    {
        return view('pages.employee.enrollment.nstp-enrollees.index', $this->searchOptions() + [
            'module' => $this->module,
            'countUrl' => route("{$this->routeName}.count"),
        ]);
    }

    public function count(Request $request)
    {
        $data = $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'schlyear' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ]);

        $rows = NstpEnrollees::counts([$data['campus']], $data['schlyear'], $data['semester']);
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
