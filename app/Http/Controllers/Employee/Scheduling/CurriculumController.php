<?php

namespace App\Http\Controllers\Employee\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Scheduling\Concerns\ScopesScheduling;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\ScheduleDB\Curriculum;
use App\Models\ScheduleDB\EnPrograms;
use App\Models\ScheduleDB\Subject;
use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Scheduling\SubjectFees;

/**
 * Scheduling > Curriculum Programs: the subjects of a program per year level and semester, in a campus.
 *  - list(): the subjects of a program in one semester; pdf(): the whole curriculum (every year and semester)
 *  - store(): units and fees come from the subject and SubjectFees, never from the form
 *  - a subject is in a program's semester once per campus
 * Subject Offered > Template copies a section's year level and semester from here. (scheduling/curricula.js)
 */
class CurriculumController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, ScopesScheduling;

    private const YEAR_LEVELS = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'];
    private const YES_NO = ['Yes' => 'Yes', 'No' => 'No'];

    public function index(Request $request)
    {
        $campus = $this->schedulingCampus($request);

        return view('pages.employee.scheduling.curricula.index', $this->schedulingOptions() + [
            'programs' => $this->programsOf($campus),
            'yearLevels' => self::YEAR_LEVELS,
            'funds' => SubjectFees::fundOptions(),
            'ojtOptions' => [
                'regular' => SubjectFees::ojtOptions(1, true),
                'summer' => SubjectFees::ojtOptions(3, true),
            ],
        ]);
    }

    // Empty until a program is picked
    public function list(Request $request)
    {
        if (! $request->filled('progCod')) {
            return response()->json(['data' => []]);
        }
        [$campus, $program, $semester] = $this->search($request);

        $rows = Curriculum::join('subjects', 'curriculum.subCode', '=', 'subjects.sub_code')
            ->where('curriculum.campus', $campus)
            ->where('curriculum.progCode', $program->progCod)
            ->where('curriculum.semester', $semester)
            ->orderBy('curriculum.yrlvl')->orderBy('subjects.sub_name')
            ->get(['curriculum.id', 'curriculum.yrlvl', 'curriculum.subCode', 'subjects.sub_name', 'subjects.sub_title', 'curriculum.lecUnit',
                'curriculum.labUnit', 'curriculum.subUnit', 'curriculum.lecFee', 'curriculum.labFee', 'curriculum.devFee',
                'curriculum.fundAccount', 'curriculum.itfee', 'curriculum.isOJT', 'curriculum.prerequisite']);

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        [$campus, $program, $semester] = $this->search($request);
        $data = $request->validate([
            'yrlvl' => ['required', 'integer', Rule::in(array_keys(self::YEAR_LEVELS))],
            'subCode' => ['required', 'string'],
            'prerequisite' => ['nullable', 'string', 'different:subCode'],
            'isOJT' => ['required', Rule::in(array_keys(SubjectFees::ojtOptions($semester, true)))],
            'isTemp' => ['required', Rule::in(array_keys(self::YES_NO))],
            'itfee' => ['required', Rule::in(array_keys(self::YES_NO))],
            'fund' => ['nullable', 'integer', Rule::in(array_keys(SubjectFees::fundOptions()))],
            'lecFee' => ['nullable', 'numeric', 'min:0', 'max:999999'],
        ], [], ['yrlvl' => 'year level', 'subCode' => 'subject', 'isOJT' => 'OJT/Thesis', 'isTemp' => 'template', 'itfee' => 'IT subject']);

        $subject = $this->offerableSubject($data['subCode'], 'subCode');
        $prerequisite = empty($data['prerequisite']) ? null : $this->offerableSubject($data['prerequisite'], 'prerequisite');

        return $this->exclusively("curricula.{$campus}", function () use ($campus, $program, $semester, $data, $subject, $prerequisite) {
            $exists = Curriculum::where('campus', $campus)->where('progCode', $program->progCod)
                ->where('semester', $semester)->where('subCode', $subject->sub_code)->exists();

            if ($exists) {
                throw ValidationException::withMessages(['subCode' => "{$subject->sub_name} is already in {$program->progAcronym}'s curriculum for this semester."]);
            }

            Curriculum::create(SubjectFees::compute($subject, $data['isOJT'], $data['lecFee'] ?? 0) + SubjectFees::fundColumns($data['fund'] ?? null) + [
                'progCode' => $program->progCod,
                'yrlvl' => $data['yrlvl'],
                'subSec' => "{$program->progAcronym} {$data['yrlvl']}",
                'semester' => $semester,
                'campus' => $campus,
                'subCode' => $subject->sub_code,
                'isOJT' => $data['isOJT'],
                'isTemp' => $data['isTemp'],
                'itfee' => $data['itfee'],
                'isType' => 'No',
                'postedBy' => $this->user()->id,
                'prerequisite' => $prerequisite?->sub_code,
            ]);

            return response()->json(['success' => true, 'message' => "{$subject->sub_name} added to {$program->progAcronym}."]);
        });
    }

    public function destroy(Curriculum $curriculum)
    {
        $this->authorizeCampus($curriculum->campus);
        abort_unless($this->programsOf(trim($curriculum->campus))->contains('progCod', $curriculum->progCode), 404, 'Curriculum subject not found.');

        $curriculum->delete();

        return response()->json(['success' => true, 'message' => "{$curriculum->subCode} removed from the curriculum."]);
    }

    // The whole curriculum of a program in a campus: per year level, 1st and 2nd semester (and Summer)
    public function pdf(Request $request)
    {
        $campus = $this->schedulingCampus($request);
        $program = $this->program($request, $campus);

        $rows = Curriculum::join('subjects', 'curriculum.subCode', '=', 'subjects.sub_code')
            ->leftJoin('subjects as prereq', 'curriculum.prerequisite', '=', 'prereq.sub_code')
            ->where('curriculum.campus', $campus)
            ->where('curriculum.progCode', $program->progCod)
            ->orderBy('curriculum.yrlvl')->orderBy('curriculum.semester')->orderBy('subjects.sub_name')
            ->get(['curriculum.yrlvl', 'curriculum.semester', 'subjects.sub_name', 'subjects.sub_title', 'subjects.sub_unit',
                'curriculum.prerequisite', 'prereq.sub_name as prerequisite_name']);

        return Pdf::loadView('pdf.curriculum', [
            'program' => $program,
            'years' => $rows->groupBy(['yrlvl', 'semester']),
            'yearLevels' => self::YEAR_LEVELS,
            'semesters' => ConfigureCurrent::SEMESTERS,
        ])->setPaper('legal', 'portrait')->stream('curriculum.pdf');
    }

    // ---------- Helpers ----------

    // The campus, program and semester of the request (program of that campus)
    private function search(Request $request): array
    {
        $campus = $this->schedulingCampus($request);
        $semester = (int) $request->validate(['semester' => ['required', 'integer', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))]])['semester'];

        return [$campus, $this->program($request, $campus), $semester];
    }

    private function program(Request $request, string $campus): EnPrograms
    {
        $code = $request->validate(['progCod' => ['required', 'string']], [], ['progCod' => 'program'])['progCod'];
        $program = $this->programsOf($campus)->firstWhere('progCod', $code);

        if (! $program) {
            throw ValidationException::withMessages(['progCod' => 'Pick a program offered in this campus.']);
        }

        return $program;
    }

    private function offerableSubject(string $code, string $field): Subject
    {
        return SchedulingLookupController::offerable()->where('sub_code', $code)->first()
            ?? throw ValidationException::withMessages([$field => "Subject {$code} does not exist or is inactive."]);
    }
}
