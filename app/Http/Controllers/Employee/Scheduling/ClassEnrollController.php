<?php

namespace App\Http\Controllers\Employee\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Scheduling\Concerns\ScopesScheduling;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\ScheduleDB\ClassEnroll;
use App\Models\ScheduleDB\SubjectOffered;

/**
 * Scheduling > Classes Enrolled: the sections (program + year & section, e.g. BSIT 1-A) of a campus and term.
 *  - list() also returns the campus's programs for the Add / Edit selects (the campus can change without a reload)
 *  - a section is unique per campus, term and program
 * Subject Offered saves the section's name as text (sub_offered.subSec = "BSIT 1-A"), so a section with offered
 * subjects keeps its program and name and cannot be deleted. (scheduling/classes.js)
 */
class ClassEnrollController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, ScopesScheduling;

    // Year level, a dash, then the section: 1-A, 2-B1, 1-A SIKAT - CAMI (with an add-on)
    private const SECTION_PATTERN = '/^[1-9]-[A-Z0-9][A-Z0-9 \-]{0,39}$/';

    public function index()
    {
        return view('pages.employee.scheduling.classes.index', $this->schedulingOptions() + [
            'addOns' => config('ciss.scheduling.section_add_ons'),
        ]);
    }

    public function list(Request $request)
    {
        $campus = $this->schedulingCampus($request);
        $term = $this->schedulingTerm($request);
        $programs = $this->programsOf($campus);

        $classes = ClassEnroll::where('campus', $campus)->where($term)
            ->whereIn('progCode', $programs->pluck('progCod'))
            ->get(['id', 'progCode', 'classSection', 'classno']);
        $acronyms = $programs->pluck('progAcronym', 'progCod');

        return response()->json([
            'data' => $classes->map(fn (ClassEnroll $class) => $class->only(['id', 'progCode', 'classSection', 'classno']) + [
                'progAcronym' => $acronyms[$class->progCode] ?? $class->progCode,
            ])->sortBy(['progAcronym', 'classSection'])->values(),
            'programs' => $programs->map->only(['progCod', 'progAcronym'])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $campus = $this->schedulingCampus($request);
        $term = $this->schedulingTerm($request);
        $data = $this->validated($request, $campus) + $request->validate([
            'addOn' => ['nullable', Rule::in(config('ciss.scheduling.section_add_ons'))],
        ]);
        $section = trim($data['classSection'] . ' ' . ($data['addOn'] ?? ''));

        return $this->exclusively("classes.{$campus}", function () use ($campus, $term, $data, $section) {
            $this->ensureUnique($campus, $term, $data['progCode'], $section);

            ClassEnroll::create($term + [
                'campus' => $campus,
                'progCode' => $data['progCode'],
                'classSection' => $section,
                'classno' => $data['classno'],
                'remember_token' => Str::random(60),
            ]);

            return response()->json(['success' => true, 'message' => "Section {$section} added."]);
        });
    }

    public function update(Request $request, ClassEnroll $class)
    {
        $this->authorizeClass($class);
        $data = $this->validated($request, $class->campus, $class);
        $term = ['schlyear' => $class->schlyear, 'semester' => $class->semester];

        return $this->exclusively("classes.{$class->campus}", function () use ($class, $term, $data) {
            $renamed = $data['progCode'] !== $class->progCode || $data['classSection'] !== $class->classSection;

            if ($renamed) {
                $this->ensureNoOfferings($class, 'its program and section cannot change');
                $this->ensureUnique($class->campus, $term, $data['progCode'], $data['classSection'], $class->id);
            }

            $class->update($data);

            return response()->json(['success' => true, 'message' => "Section {$class->classSection} saved."]);
        });
    }

    public function destroy(ClassEnroll $class)
    {
        $this->authorizeClass($class);
        $this->ensureNoOfferings($class, 'it cannot be deleted');

        $class->delete();

        return response()->json(['success' => true, 'message' => "Section {$class->classSection} deleted."]);
    }

    // ---------- Helpers ----------

    // $existing (Edit): a section name typed as it is saved (any case) stays exactly as saved, even an older name
    // that does not follow SECTION_PATTERN ("3 - A", "2-B Special Class"); a new name must follow it
    private function validated(Request $request, string $campus, ?ClassEnroll $existing = null): array
    {
        $section = strtoupper(preg_replace('/\s+/', ' ', trim((string) $request->input('classSection'))));
        $unchanged = $existing && $section === strtoupper(preg_replace('/\s+/', ' ', trim((string) $existing->classSection)));
        $request->merge(['classSection' => $unchanged ? $existing->classSection : $section]);

        return $request->validate([
            'progCode' => ['required', Rule::in($this->programsOf($campus)->pluck('progCod')->all())],
            'classSection' => $unchanged ? ['required'] : ['required', 'regex:' . self::SECTION_PATTERN],
            'classno' => ['required', 'integer', 'min:0', 'max:500'],
        ], [
            'progCode.in' => 'Pick a program offered in this campus.',
            'classSection.regex' => 'The year & section must look like 1-A (year level, a dash, then the section).',
        ], ['progCode' => 'program', 'classSection' => 'year & section', 'classno' => 'estimated number of students']);
    }

    private function ensureUnique(string $campus, array $term, string $progCode, string $section, ?int $exceptId = null): void
    {
        $taken = ClassEnroll::where('campus', $campus)->where($term)
            ->where('progCode', $progCode)->where('classSection', $section)
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages(['classSection' => "Section {$section} of this program already exists in this term."]);
        }
    }

    // Only sections of a campus the user may manage, of a program the user may see (Graduate School Staff)
    private function authorizeClass(ClassEnroll $class): void
    {
        $this->authorizeCampus($class->campus);

        abort_unless($this->programsOf($class->campus)->contains('progCod', $class->progCode), 404, 'Section not found.');
    }

    private function ensureNoOfferings(ClassEnroll $class, string $consequence): void
    {
        $name = $this->programsOf($class->campus)->firstWhere('progCod', $class->progCode)->progAcronym . ' ' . $class->classSection;

        $offered = SubjectOffered::where('campus', $class->campus)
            ->where('schlyear', $class->schlyear)->where('semester', $class->semester)
            ->where('subSec', $name)
            ->exists();

        if ($offered) {
            throw ValidationException::withMessages(['classSection' => "{$name} already has subjects offered, so {$consequence}."]);
        }
    }
}
