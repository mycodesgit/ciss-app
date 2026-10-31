<?php

namespace App\Http\Controllers\Employee\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Scheduling\Concerns\ScopesScheduling;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\EnrollmentDB\StudentLevel;
use App\Models\ScheduleDB\ClassEnroll;
use App\Models\ScheduleDB\College;
use App\Models\ScheduleDB\Curriculum;
use App\Models\ScheduleDB\Department;
use App\Models\ScheduleDB\EnPrograms;
use App\Support\CampusList;

/**
 * Scheduling > Programs (administrators): every program, its college, department, name, acronym, level and campuses.
 *  - the Edit modal's departments come from SchedulingLookupController::departments
 *  - update(): the program code ({college}-{dept}-{001}) and account ("TUITION - {college}") are made by the server
 *    when the college or department changes, and only while no class or curriculum uses the old code
 *    (class_enroll / curriculum point to programs.progCod). (scheduling/programs.js)
 */
class ProgramController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, ScopesScheduling;

    public function index()
    {
        return view('pages.employee.scheduling.programs.index', [
            'colleges' => $this->colleges(),
            'levels' => StudentLevel::orderBy('id')->pluck('studLevel', 'id'),
            'campuses' => config('ciss.campuses'),
        ]);
    }

    public function list()
    {
        $programs = EnPrograms::orderBy('progAcronym')
            ->get(['id', 'progCod', 'progAcronym', 'progName', 'progCollege', 'progDep', 'progAccount', 'progLev', 'campus']);

        return response()->json([
            'data' => $programs->map(fn (EnPrograms $program) => $program->only(['id', 'progCod', 'progAcronym', 'progName', 'progCollege', 'progDep', 'progAccount', 'progLev'])
                + ['campuses' => CampusList::split($program->campus)]),
        ]);
    }

    public function update(Request $request, EnPrograms $program)
    {
        $data = $request->validate([
            'progCollege' => ['required', Rule::in($this->colleges()->keys()->all())],
            'progDep' => ['required', 'string', Rule::exists(Department::class, 'deptCod')->where('collegeCod', (string) $request->input('progCollege'))],
            'progName' => ['required', 'string', 'max:255'],
            'progAcronym' => ['required', 'string', 'max:100', Rule::unique(EnPrograms::class, 'progAcronym')->ignore($program->id)],
            'progLev' => ['required', Rule::exists(StudentLevel::class, 'id')],
            'campus' => ['required', 'array', 'min:1'],
            'campus.*' => ['distinct', Rule::in(array_keys(config('ciss.campuses')))],
        ], ['progDep.exists' => 'Pick a department of the selected college.'], [
            'progCollege' => 'college', 'progDep' => 'department', 'progName' => 'program name',
            'progAcronym' => 'program acronym', 'progLev' => 'program level',
        ]);

        return $this->exclusively('programs', function () use ($program, $data) {
            $moved = $data['progCollege'] !== $program->progCollege || $data['progDep'] !== $program->progDep;

            if ($moved) {
                $this->ensureCodeUnused($program);
                $data['progCod'] = $this->nextCode($data['progCollege'], $data['progDep']);
                $data['progAccount'] = 'TUITION - ' . $data['progCollege'];
            }

            $program->update(array_merge($data, ['campus' => implode(',', $data['campus'])]));

            return response()->json(['success' => true, 'message' => "Program {$program->progAcronym} saved."]);
        });
    }

    // ---------- Helpers ----------

    // [college_abbr => college_name] of the academic colleges
    private function colleges()
    {
        return College::whereIn('id', config('ciss.scheduling.college_ids'))->orderBy('college_name')->pluck('college_name', 'college_abbr');
    }

    private function ensureCodeUnused(EnPrograms $program): void
    {
        $used = ClassEnroll::where('progCode', $program->progCod)->exists() || Curriculum::where('progCode', $program->progCod)->exists();

        if ($used) {
            throw ValidationException::withMessages(['progCollege' => "The college or department of {$program->progAcronym} cannot change: its program code {$program->progCod} is already used by classes or curricula."]);
        }
    }

    // {college}-{dept}-{last number + 1, 3 digits}
    private function nextCode(string $college, string $department): string
    {
        $prefix = "{$college}-{$department}-";
        $last = EnPrograms::where('progCod', 'like', addcslashes($prefix, '%_\\') . '%')->pluck('progCod')
            ->map(fn ($code) => (int) substr($code, strlen($prefix)))
            ->max() ?? 0;

        return $prefix . str_pad($last + 1, 3, '0', STR_PAD_LEFT);
    }
}
