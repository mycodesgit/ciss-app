<?php

namespace App\Http\Controllers\Employee\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Scheduling\Concerns\ScopesScheduling;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\EnrollmentDB\Grade;
use App\Models\ScheduleDB\ClassEnroll;
use App\Models\ScheduleDB\Curriculum;
use App\Models\ScheduleDB\SetClassSchedule;
use App\Models\ScheduleDB\Subject;
use App\Models\ScheduleDB\SubjectOffered;
use App\Services\Scheduling\SubjectFees;

/**
 * Scheduling > Subject Offered: the subjects offered to the sections of a campus in a term (sub_offered).
 *  - list(): the offerings, and the term's sections for the Add / Edit selects
 *  - store() / update(): units and fees come from the subject and SubjectFees, never from the form; who posted
 *    it and when are set by the server. An offering is unique per section, subject and type in a term.
 *  - an offering with enrolled students keeps its subject and section and cannot be deleted; one with a class
 *    schedule cannot be deleted either
 *  - template() / storeTemplate(): campuses of config ciss.scheduling.template_campuses add a section's subjects
 *    from its program's curriculum (year level + semester); the server reads the curriculum again when saving and
 *    skips subjects already offered, all in one transaction
 * Graduate School Staff see graduate school subjects only. (scheduling/offerings.js)
 */
class SubjectOfferedController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, ScopesScheduling;

    private const YES_NO = ['Yes' => 'Yes', 'No' => 'No'];
    private const KEEP_FUND = 'keep';

    public function index()
    {
        return view('pages.employee.scheduling.offerings.index', $this->schedulingOptions() + [
            'types' => config('ciss.scheduling.offering_types'),
            'funds' => SubjectFees::fundOptions(),
            'ojtOptions' => ['regular' => SubjectFees::ojtOptions(1), 'summer' => SubjectFees::ojtOptions(3)],
            'maxStudents' => config('ciss.scheduling.max_students'),
            'templateCampuses' => config('ciss.scheduling.template_campuses'),
        ]);
    }

    public function list(Request $request)
    {
        $campus = $this->schedulingCampus($request);
        $term = $this->schedulingTerm($request);

        $offerings = $this->offeringsOf($campus, $term)
            ->join('subjects', 'sub_offered.subCode', '=', 'subjects.sub_code')
            ->orderBy('sub_offered.subSec')->orderBy('subjects.sub_name')
            ->get(['sub_offered.id', 'sub_offered.subCode', 'subjects.sub_name', 'subjects.sub_title', 'sub_offered.subSec',
                'sub_offered.lecUnit', 'sub_offered.labUnit', 'sub_offered.subUnit', 'sub_offered.maxstud', 'sub_offered.lecFee',
                'sub_offered.labFee', 'sub_offered.devFee', 'sub_offered.isType', 'sub_offered.isTemp', 'sub_offered.isOJT',
                'sub_offered.fund', 'sub_offered.fundAccount', 'sub_offered.itfee']);

        $fundIds = array_flip(SubjectFees::fundOptions());

        return response()->json([
            'data' => $offerings->map(fn ($offering) => $offering->toArray() + [
                // The Fund select's value: the account id when it is one of the choices
                'fund_id' => $fundIds["{$offering->fund} - {$offering->fundAccount}"] ?? null,
                'graduate' => SubjectFees::isGraduate($offering->subCode),
            ]),
            'sections' => $this->sectionsOf($campus, $term)->values(),
        ]);
    }

    public function store(Request $request)
    {
        $campus = $this->schedulingCampus($request);
        $term = $this->schedulingTerm($request);
        [$subject, $data] = $this->validated($request, $campus, $term);

        return $this->exclusively("offerings.{$campus}", function () use ($campus, $term, $subject, $data) {
            $this->ensureUnique($campus, $term, $subject->sub_code, $data);

            SubjectOffered::create($this->columns($subject, $data) + $term + ['campus' => $campus]);

            return response()->json(['success' => true, 'message' => "{$subject->sub_name} offered to {$data['subSec']}."]);
        });
    }

    public function update(Request $request, SubjectOffered $offering)
    {
        $this->authorizeOffering($offering);
        $term = ['schlyear' => $offering->schlyear, 'semester' => (int) $offering->semester];
        [$subject, $data] = $this->validated($request, $offering->campus, $term);

        return $this->exclusively("offerings.{$offering->campus}", function () use ($offering, $term, $subject, $data) {
            $moved = $subject->sub_code !== $offering->subCode || $data['subSec'] !== $offering->subSec;

            if ($moved && $this->hasStudents($offering)) {
                throw ValidationException::withMessages(['subCode' => 'Students are enrolled in this subject, so its subject and section cannot change.']);
            }
            $this->ensureUnique($offering->campus, $term, $subject->sub_code, $data, $offering->id);

            $offering->update($this->columns($subject, $data));

            return response()->json(['success' => true, 'message' => "{$subject->sub_name} of {$data['subSec']} saved."]);
        });
    }

    public function destroy(SubjectOffered $offering)
    {
        $this->authorizeOffering($offering);

        if ($this->hasStudents($offering)) {
            throw ValidationException::withMessages(['id' => 'Students are enrolled in this subject, so it cannot be deleted.']);
        }
        if (SetClassSchedule::where('subject_id', $offering->id)->exists()) {
            throw ValidationException::withMessages(['id' => 'This subject has a class schedule. Remove the schedule first.']);
        }

        $offering->delete();

        return response()->json(['success' => true, 'message' => "{$offering->subCode} of {$offering->subSec} deleted."]);
    }

    // The curriculum subjects of a section's year level and semester, marked when already offered to the section
    public function template(Request $request)
    {
        [$section, $subjects] = $this->templateOf($request);

        return response()->json([
            'section' => $section['name'],
            'data' => $subjects->map(fn ($row) => $row->only(['subCode', 'sub_name', 'sub_title', 'lecUnit', 'labUnit', 'subUnit',
                'lecFee', 'labFee', 'devFee', 'itfee', 'fundAccount', 'isOJT', 'isTemp', 'isType', 'offered'])),
        ]);
    }

    public function storeTemplate(Request $request)
    {
        [$section, $subjects, $campus, $term] = $this->templateOf($request);
        $new = $subjects->reject->offered;

        if ($new->isEmpty()) {
            throw ValidationException::withMessages(['class' => "Every curriculum subject of {$section['name']} is already offered."]);
        }

        return $this->exclusively("offerings.{$campus}", function () use ($section, $new, $campus, $term) {
            DB::connection('schedule')->transaction(function () use ($section, $new, $campus, $term) {
                foreach ($new as $row) {
                    SubjectOffered::create($term + [
                        'campus' => $campus,
                        'subCode' => $row->subCode,
                        'subSec' => $section['name'],
                        'lecUnit' => $row->lecUnit ?? 0,
                        'labUnit' => $row->labUnit ?? 0,
                        'subUnit' => $row->subUnit ?? 0,
                        'lecFee' => $row->lecFee ?? 0,
                        'labFee' => $row->labFee ?? 0,
                        'devFee' => $row->devFee ?? 0,
                        'itfee' => $row->itfee ?: 'No',
                        'fund' => $row->fund,
                        'fundAccount' => $row->fundAccount,
                        'isOJT' => $row->isOJT ?: SubjectFees::NO,
                        'isTemp' => $row->isTemp ?: 'No',
                        'isType' => $row->isType ?: 'No',
                        'maxstud' => config('ciss.scheduling.max_students'),
                        'postedBy' => $this->user()->id,
                        'datePosted' => now()->toDateString(),
                    ]);
                }
            });

            return response()->json(['success' => true, 'message' => $new->count() . " subject(s) offered to {$section['name']}."]);
        });
    }

    // ---------- Helpers ----------

    // [Subject, validated form] of an Add / Edit; the section must be one of the campus's sections in the term
    private function validated(Request $request, string $campus, array $term): array
    {
        $data = $request->validate([
            'subCode' => ['required', 'string'],
            'subSec' => ['required', Rule::in($this->sectionsOf($campus, $term)->pluck('name')->all())],
            'maxstud' => ['required', 'integer', 'min:1', 'max:500'],
            'isTemp' => ['required', Rule::in(array_keys(self::YES_NO))],
            'isOJT' => ['required', Rule::in(array_keys(SubjectFees::ojtOptions($term['semester'])))],
            'isType' => ['required', Rule::in(array_keys(config('ciss.scheduling.offering_types')))],
            'itfee' => ['required', Rule::in(array_keys(self::YES_NO))],
            // An account id, empty for No Account, or "keep" (Edit: an older account that is no longer a choice)
            'fund' => ['nullable', Rule::in([...array_keys(SubjectFees::fundOptions()), self::KEEP_FUND])],
            'lecFee' => ['nullable', 'numeric', 'min:0', 'max:999999'],
        ], ['subSec.in' => 'Pick a section of this campus and term (Classes Enrolled).'], [
            'subCode' => 'subject', 'subSec' => 'year & section', 'maxstud' => 'max students', 'isTemp' => 'template',
            'isOJT' => 'OJT/Thesis', 'isType' => 'type', 'itfee' => 'IT subject',
        ]);

        $subject = SchedulingLookupController::offerable()->where('sub_code', $data['subCode'])->first();
        if (! $subject || ! $this->maySee($subject->sub_code)) {
            throw ValidationException::withMessages(['subCode' => "Subject {$data['subCode']} does not exist or is inactive."]);
        }

        return [$subject, $data];
    }

    // sub_offered columns of an Add / Edit ("keep" leaves fund / fundAccount as they are)
    private function columns(Subject $subject, array $data): array
    {
        $fund = $data['fund'] ?? null;
        $fundColumns = $fund === self::KEEP_FUND ? [] : SubjectFees::fundColumns($fund ? (int) $fund : null);

        return SubjectFees::compute($subject, $data['isOJT'], $data['lecFee'] ?? 0) + $fundColumns + [
            'subCode' => $subject->sub_code,
            'subSec' => $data['subSec'],
            'maxstud' => $data['maxstud'],
            'isTemp' => $data['isTemp'],
            'isOJT' => $data['isOJT'],
            'isType' => $data['isType'],
            'itfee' => $data['itfee'],
            'postedBy' => $this->user()->id,
            'datePosted' => now()->toDateString(),
        ];
    }

    private function ensureUnique(string $campus, array $term, string $subjectCode, array $data, ?int $exceptId = null): void
    {
        $taken = SubjectOffered::where('campus', $campus)->where($term)
            ->where('subCode', $subjectCode)->where('subSec', $data['subSec'])->where('isType', $data['isType'])
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages(['subCode' => "{$subjectCode} is already offered to {$data['subSec']} ({$data['isType']}) in this term."]);
        }
    }

    private function offeringsOf(string $campus, array $term)
    {
        return SubjectOffered::where('sub_offered.campus', $campus)
            ->where('sub_offered.schlyear', $term['schlyear'])
            ->where('sub_offered.semester', $term['semester'])
            ->when($this->user()->isGraduateSchoolStaff(), fn ($query) => $query->where('sub_offered.subCode', 'like', '%' . config('ciss.scheduling.graduate_program_code') . '%'));
    }

    // The sections of a campus in a term: [id, name ("BSIT 1-A"), progCode, year]
    private function sectionsOf(string $campus, array $term)
    {
        $programs = $this->programsOf($campus)->keyBy('progCod');

        return ClassEnroll::where('campus', $campus)->where($term)
            ->whereIn('progCode', $programs->keys())
            ->get(['id', 'progCode', 'classSection'])
            ->map(fn (ClassEnroll $class) => [
                'id' => $class->id,
                'name' => "{$programs[$class->progCode]->progAcronym} {$class->classSection}",
                'progCode' => $class->progCode,
                'year' => (int) strtok($class->classSection, '-'),
            ])
            ->sortBy('name');
    }

    // [section, curriculum rows (with "offered"), campus, term] of a Template request
    private function templateOf(Request $request): array
    {
        $campus = $this->schedulingCampus($request);
        abort_unless(in_array($campus, config('ciss.scheduling.template_campuses'), true), 403, 'Templates are not available for this campus.');

        $term = $this->schedulingTerm($request);
        $classId = (int) $request->validate(['class' => ['required', 'integer']], [], ['class' => 'year & section'])['class'];
        $section = $this->sectionsOf($campus, $term)->firstWhere('id', $classId)
            ?? throw ValidationException::withMessages(['class' => 'Pick a section of this campus and term.']);

        $offered = $this->offeringsOf($campus, $term)->where('subSec', $section['name'])->pluck('subCode')->flip();

        $subjects = Curriculum::join('subjects', 'curriculum.subCode', '=', 'subjects.sub_code')
            ->where('curriculum.progCode', $section['progCode'])
            ->where('curriculum.campus', $campus)
            ->where('curriculum.semester', $term['semester'])
            ->where('curriculum.subSec', 'like', '% ' . $section['year'])
            ->orderBy('subjects.sub_name')
            ->get(['curriculum.*', 'subjects.sub_name', 'subjects.sub_title'])
            ->filter(fn ($row) => $this->maySee($row->subCode))
            ->each(fn ($row) => $row->offered = $offered->has($row->subCode))
            ->values();

        return [$section, $subjects, $campus, $term];
    }

    // Graduate School Staff: graduate school subjects only
    private function maySee(string $subjectCode): bool
    {
        return ! $this->user()->isGraduateSchoolStaff() || str_contains($subjectCode, config('ciss.scheduling.graduate_program_code'));
    }

    private function authorizeOffering(SubjectOffered $offering): void
    {
        $this->authorizeCampus($offering->campus);
        abort_unless($this->maySee($offering->subCode), 404, 'Subject offered not found.');
    }

    private function hasStudents(SubjectOffered $offering): bool
    {
        return Grade::where('subjID', $offering->id)->exists();
    }
}
