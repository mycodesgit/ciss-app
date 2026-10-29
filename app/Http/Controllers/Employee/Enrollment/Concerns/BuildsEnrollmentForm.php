<?php

namespace App\Http\Controllers\Employee\Enrollment\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\AssessmentDB\StudentAppraisal;
use App\Models\EnrollmentDB\Grade;
use App\Models\EnrollmentDB\MajorMinor;
use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\StudentLevel;
use App\Models\EnrollmentDB\StudentShifTrans;
use App\Models\EnrollmentDB\StudentStatus;
use App\Models\EnrollmentDB\StudentType;
use App\Models\EnrollmentDB\Student;
use App\Models\ScheduleDB\ClassEnroll;
use App\Models\ScheduleDB\SubjectOffered;
use App\Models\ScholarshipDB\Scholar;
use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Enrollment\FeeAssessment;

/**
 * The enrollment form shared by Enroll Student and Edit Enrollment (partials/enrollment-form.blade.php):
 * option lists, the picked section and subjects, seat checks, fee assessment and the saved appraisal.
 * Every choice the browser sends is checked against the term and the user's campus here.
 * Edit Enrollment passes the current enrollment ($current) so the student's own seat / subjects don't count.
 * Uses ChecksEnrollment (user, campuses, graduate staff).
 */
trait BuildsEnrollmentForm
{
    // Graduate School staff can only pick these student levels
    protected array $graduateLevels = [80, 90];

    // Lists of the form's selects
    protected function formOptions(ConfigureCurrent $term): array
    {
        return [
            'classSections' => $this->classSections($term),
            'offeredSubjects' => $this->offeredSubjects($term)->orderBy('subjects.sub_name')->orderBy('sub_offered.subSec')->get()->map(fn ($subject) => $this->subjectRow($subject)),
            'levels' => StudentLevel::when($this->isGraduateStaff(), fn ($query) => $query->whereIn('id', $this->graduateLevels))->get(['id', 'studLevel']),
            'scholarships' => Scholar::orderBy('scholar_name')->get(['id', 'scholar_name']),
            'majorMinors' => MajorMinor::get(['id', 'submamiID', 'submamiName']),
            'statuses' => StudentStatus::get(['id', 'studentStatName']),
            'types' => StudentType::get(['id', 'studentTypeName']),
            'shiftTrans' => StudentShifTrans::get(['id', 'studentShiftTransDesc']),
        ];
    }

    // Course Year & Section picked: refuse a full section, otherwise its template subjects
    protected function sectionSubjectsResponse(Request $request, ConfigureCurrent $term, ?StudEnrolmentHistory $current = null)
    {
        $class = $this->classSection($request, $term);
        $capacity = $this->sectionCapacity($class, $term, $current);

        if ($capacity['isFull'] && ! $this->isCurrentSection($class, $current)) {
            $this->failField('class_id', "{$class->course} is full ({$capacity['classNo']} students).");
        }

        $subjects = $this->offeredSubjects($term)
            ->where('sub_offered.subSec', $class->course)
            ->where('sub_offered.isTemp', 'Yes')
            ->orderBy('sub_offered.subCode')
            ->get();

        return response()->json([
            'class' => ['programName' => $class->progName, 'yearLevel' => $class->yearleveldesc],
            'capacity' => $capacity,
            'subjects' => $subjects->map(fn ($subject) => $this->subjectRow($subject)),
        ]);
    }

    /**
     * Checks the form and computes the fees: [class, subjects, fees, choices].
     * $forSave also checks the level, scholarship, major / minor and 4P's (Assess only needs what changes the fees).
     */
    protected function enrollmentInput(Request $request, Student $student, ConfigureCurrent $term, bool $forSave): array
    {
        $rules = [
            'class_id' => ['required', 'integer'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'distinct'],
            'studStatus' => ['required', Rule::exists(StudentStatus::class, 'id')],
            'studType' => ['required', Rule::exists(StudentType::class, 'id')],
            'transferee' => ['required', Rule::exists(StudentShifTrans::class, 'id')],
        ];

        if ($forSave) {
            $rules += [
                'studLevel' => ['required', Rule::exists(StudentLevel::class, 'id'), Rule::when($this->isGraduateStaff(), Rule::in($this->graduateLevels))],
                'studSch' => ['required', Rule::exists(Scholar::class, 'id')],
                'studMajor' => ['required', Rule::exists(MajorMinor::class, 'submamiID')],
                'studMinor' => ['required', Rule::exists(MajorMinor::class, 'submamiID')],
                'fourPs' => ['required', Rule::in(['0', '1'])],
            ];
        }

        $data = $request->validate($rules, ['subject_ids.required' => 'Add at least one subject.'], [
            'class_id' => 'Course Year & Section', 'studStatus' => 'Status', 'studType' => 'Type', 'transferee' => 'Transferee/Shiftee',
            'studLevel' => 'Student Level', 'studSch' => 'Scholarship', 'studMajor' => 'Major', 'studMinor' => 'Minor', 'fourPs' => "4P's Beneficiary",
        ]);

        $class = $this->classSection($request, $term);

        $subjects = $this->offeredSubjects($term)->whereIn('sub_offered.id', $data['subject_ids'])->get();
        if ($subjects->count() !== count($data['subject_ids'])) {
            $this->failField('subject_ids', 'Some subjects are not offered in your campus this term. Remove them and add them again.');
        }

        // Graduate students (ID with -G) are assessed by type (1 new / 2 old) instead of year level
        $yearLevel = str_contains($student->stud_id, '-G') ? (int) $data['studType'] : (int) $class->studYear;

        $fees = FeeAssessment::assess($class->progCode, $yearLevel, $term, $class->campus, $subjects,
            (int) $data['studStatus'], (int) $data['studType'], (int) $data['transferee']);

        if (! $fees['lines']) {
            $this->failField('class_id', 'No student fee is set for this program and year level. Please contact the Assessment Office.');
        }

        return [
            'class' => $class,
            'subjects' => $subjects,
            'fees' => $fees,
            'choices' => collect($data)->only(['studLevel', 'studSch', 'studMajor', 'studMinor', 'studStatus', 'studType', 'transferee', 'fourPs'])->all(),
        ];
    }

    // program_en_history / studhislog values of the form (the caller adds postedBy, status, encode)
    protected function enrollmentRecord(Student $student, ConfigureCurrent $term, array $input): array
    {
        $class = $input['class'];

        return [
            'studentID' => $student->stud_id,
            'schlyear' => $term->schlyear,
            'semester' => $term->semester,
            'campus' => $class->campus,
            'course' => $class->course,
            'progCod' => $class->progCode,
            'studYear' => $class->studYear,
            'studSec' => $class->studSec,
            'studClassID' => $class->id,
            'studUnit' => $input['fees']['totals']['units'],
            'postedDate' => now()->toDateString(),
        ] + $input['choices'];
    }

    // Replaces the student's appraisal of the term with the assessed lines ($previousCampus: editing moved the campus)
    protected function saveAppraisal(Student $student, ConfigureCurrent $term, string $campus, array $fees, ?string $previousCampus = null): void
    {
        StudentAppraisal::where('studID', $student->stud_id)
            ->where('schlyear', $term->schlyear)
            ->where('semester', $term->semester)
            ->whereIn('campus', array_filter([$campus, $previousCampus]))
            ->delete();

        foreach (array_filter($fees['lines'], fn ($line) => $line['saved']) as $line) {
            StudentAppraisal::create([
                'studID' => $student->stud_id,
                'schlyear' => $term->schlyear,
                'semester' => $term->semester,
                'campus' => $campus,
                'fundID' => $line['fund'],
                'account' => $line['account'],
                'amount' => $line['amount'],
                'dateAssess' => now()->toDateString(),
                'postedBy' => $this->user()->id,
            ]);
        }
    }

    // The three databases a save writes, as one unit: any error rolls all of them back
    protected function inEnrollmentTransaction(callable $callback)
    {
        return DB::connection('schedule')->transaction(fn () => DB::connection('enrollment')->transaction(fn () => DB::connection('assessment')->transaction($callback)));
    }

    // ---------- Checks ----------

    // The picked class section, which must be one of the user's for the term
    protected function classSection(Request $request, ConfigureCurrent $term)
    {
        $request->validate(['class_id' => ['required', 'integer']], [], ['class_id' => 'Course Year & Section']);

        $class = $this->classSections($term)->firstWhere('id', (int) $request->input('class_id'));

        if (! $class) {
            $this->failField('class_id', 'The selected Course Year & Section is not offered in your campus this term.');
        }

        return $class;
    }

    /**
     * Section and subjects must still have seats (called inside the save's locks).
     * Editing: staying in the same section and keeping a subject need no new seat ($keptSubjectIds).
     */
    protected function ensureSeats($class, ConfigureCurrent $term, $subjects, ?StudEnrolmentHistory $current = null, array $keptSubjectIds = []): void
    {
        $capacity = $this->sectionCapacity($class, $term, $current);
        if ($capacity['isFull'] && ! $this->isCurrentSection($class, $current)) {
            $this->failField('class_id', "{$class->course} is full ({$capacity['classNo']} students).");
        }

        $newSubjects = $subjects->reject(fn ($subject) => in_array((int) $subject->id, $keptSubjectIds, true));
        $taken = Grade::whereIn('subjID', $newSubjects->pluck('id'))->groupBy('subjID')->selectRaw('subjID, COUNT(*) as total')->pluck('total', 'subjID');
        $full = $newSubjects->filter(fn ($subject) => ($taken[$subject->id] ?? 0) >= (int) $subject->maxstud);

        if ($full->isNotEmpty()) {
            throw ValidationException::withMessages([
                'subject_ids' => $full->map(fn ($subject) => "{$subject->sub_name} - {$subject->subSec} is full ({$subject->maxstud} students).")->values()->all(),
            ]);
        }
    }

    // Students in the section vs its capacity (class_enroll.classno), not counting the enrollment being edited
    protected function sectionCapacity($class, ConfigureCurrent $term, ?StudEnrolmentHistory $current = null): array
    {
        $enrolled = StudEnrolmentHistory::where('schlyear', $term->schlyear)
            ->where('semester', $term->semester)
            ->where('campus', $class->campus)
            ->where('progCod', $class->progCode)
            ->where('studYear', $class->studYear)
            ->where('studSec', $class->studSec)
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))
            ->count();

        return ['enrolled' => $enrolled, 'classNo' => (int) $class->classno, 'isFull' => $enrolled >= (int) $class->classno];
    }

    protected function isCurrentSection($class, ?StudEnrolmentHistory $current): bool
    {
        return $current && (int) $current->studClassID === (int) $class->id;
    }

    protected function failField(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }

    // ---------- Lists ----------

    // Course Year & Section options of the term and the user's campus (Graduate School staff: GSS programs only)
    protected function classSections(ConfigureCurrent $term)
    {
        $yearLevels = config('database.connections.enrollment.database') . '.yearlevel';

        return ClassEnroll::join('programs', 'class_enroll.progCode', '=', 'programs.progCod')
            ->join("{$yearLevels} as yearlevel", DB::raw("SUBSTRING_INDEX(class_enroll.classSection, '-', 1)"), '=', 'yearlevel.yearsection')
            ->where('class_enroll.schlyear', $term->schlyear)
            ->where('class_enroll.semester', $term->semester)
            ->whereIn('class_enroll.campus', $this->campuses())
            ->when($this->isGraduateStaff(), fn ($query) => $query->where('class_enroll.progCode', 'LIKE', '%-GSS-%'))
            ->orderBy('programs.progAcronym')
            ->orderBy('class_enroll.classSection')
            ->get([
                'class_enroll.id', 'class_enroll.campus', 'class_enroll.progCode', 'class_enroll.classSection', 'class_enroll.classno',
                'programs.progAcronym', 'programs.progName', 'yearlevel.yearleveldesc',
            ])
            ->each(function ($class) {
                // "BSIT 1-A": the value of sub_offered.subSec and program_en_history.course
                $class->course = $class->progAcronym . ' ' . $class->classSection;
                [$class->studYear, $class->studSec] = array_pad(explode('-', $class->classSection, 2), 2, '');
            });
    }

    // Subjects offered in the term and the user's campus
    protected function offeredSubjects(ConfigureCurrent $term)
    {
        return SubjectOffered::join('subjects', 'sub_offered.subCode', '=', 'subjects.sub_code')
            ->where('sub_offered.schlyear', $term->schlyear)
            ->where('sub_offered.semester', $term->semester)
            ->whereIn('sub_offered.campus', $this->campuses())
            ->select([
                'sub_offered.id', 'sub_offered.subCode', 'sub_offered.subSec', 'sub_offered.isType', 'sub_offered.subUnit',
                'sub_offered.lecFee', 'sub_offered.labFee', 'sub_offered.devFee', 'sub_offered.itfee', 'sub_offered.maxstud',
                'subjects.sub_name', 'subjects.sub_title',
            ]);
    }

    // A subject as shown in the form's Subjects table
    protected function subjectRow($subject): array
    {
        return [
            'id' => $subject->id,
            'code' => $subject->subCode,
            'name' => trim($subject->sub_name . ' - ' . $subject->subSec . ' ' . $subject->isType),
            'title' => $subject->sub_title,
            'units' => (float) $subject->subUnit,
            'lecFee' => (float) $subject->lecFee,
            'labFee' => (float) $subject->labFee,
            'devFee' => (float) $subject->devFee,
            'itSubject' => $subject->itfee === 'Yes',
        ];
    }
}
