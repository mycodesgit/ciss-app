<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
use App\Models\EnrollmentDB\StudHisLog;
use App\Models\EnrollmentDB\StudSubLog;
use App\Models\ScheduleDB\ClassEnroll;
use App\Models\ScheduleDB\SubjectOffered;
use App\Models\ScholarshipDB\Scholar;
use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Enrollment\FeeAssessment;

/**
 * Enrollment > Enroll Student, the enrollment form of one student for one term.
 *  - create():          the form (opened from the search; a direct URL goes through the same checks)
 *  - sectionSubjects(): AJAX, picking a Course Year & Section: its capacity and its template subjects
 *  - assess():          AJAX, the fee assessment of the chosen section, subjects and student status / type
 *  - store():           saves the enrollment
 *
 * The browser only sends choices (class section id, subject ids, level, scholarship, ...). Everything else
 * (program, year / section, units, fees, campus, posted by / date) is taken or computed here, and every
 * choice is checked against this term and campus. (public/js/employee/enrollment/enroll-form.js)
 */
class EnrollmentFormController extends Controller
{
    use ChecksEnrollment;

    private const ENROLLED_STATUS = 2;          // program_en_history.status of an enrollment saved here
    private const DEFAULT_LEVEL = 50;
    private const DEFAULT_MAJOR_MINOR = 43;     // majorminor_subject.id selected by default
    private const DEFAULT_TRANSFEREE = 3;
    private const GRADUATE_LEVELS = [80, 90];

    public function create(Request $request, string $student)
    {
        try {
            [$student, $term] = $this->enrollableStudent($request->merge(['stud_id' => $student]));
        } catch (ValidationException $e) {
            return redirect()->route('employee.enrollment.enroll.index')->with('error', collect($e->errors())->flatten()->first());
        }

        return view('pages.employee.enrollment.studenroll.enrollstudent', [
            'student' => $student,
            'term' => $term,
            'classSections' => $this->classSections($term),
            'offeredSubjects' => $this->offeredSubjects($term)->orderBy('subjects.sub_name')->orderBy('sub_offered.subSec')->get()->map(fn ($subject) => $this->subjectRow($subject)),
            'levels' => StudentLevel::when($this->isGraduateStaff(), fn ($query) => $query->whereIn('id', self::GRADUATE_LEVELS))->get(['id', 'studLevel']),
            'scholarships' => Scholar::orderBy('scholar_name')->get(['id', 'scholar_name']),
            'majorMinors' => MajorMinor::get(['id', 'submamiID', 'submamiName']),
            'statuses' => StudentStatus::get(['id', 'studentStatName']),
            'types' => StudentType::get(['id', 'studentTypeName']),
            'shiftTrans' => StudentShifTrans::get(['id', 'studentShiftTransDesc']),
            // Defaults: the scholarship of the student's last enrollment, and the old form's preselected values
            'defaults' => [
                'level' => $this->isGraduateStaff() ? null : self::DEFAULT_LEVEL,
                'scholarship' => StudEnrolmentHistory::where('studentID', $student->stud_id)->orderByDesc('id')->value('studSch'),
                'majorMinor' => MajorMinor::whereKey(self::DEFAULT_MAJOR_MINOR)->value('submamiID'),
                'transferee' => self::DEFAULT_TRANSFEREE,
            ],
        ]);
    }

    // Course Year & Section picked: refuse a full section, otherwise its template subjects
    public function sectionSubjects(Request $request, string $student)
    {
        [, $term] = $this->enrollableStudent($request->merge(['stud_id' => $student]));
        $class = $this->classSection($request, $term);
        $capacity = $this->sectionCapacity($class, $term);

        if ($capacity['isFull']) {
            $this->failField('class_id', "{$class->progAcronym} {$class->classSection} is full ({$capacity['classNo']} students).");
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

    public function assess(Request $request, string $student)
    {
        [$student, $term] = $this->enrollableStudent($request->merge(['stud_id' => $student]));

        return response()->json($this->assessment($request, $student, $term, false)['fees']);
    }

    public function store(Request $request, string $student)
    {
        [$student, $term] = $this->enrollableStudent($request->merge(['stud_id' => $student]));
        $enrollment = $this->assessment($request, $student, $term, true);
        ['class' => $class, 'subjects' => $subjects, 'fees' => $fees, 'choices' => $choices] = $enrollment;

        $user = $this->user();
        $today = now()->toDateString();
        $encode = now()->format('Ymd') . '-' . strtoupper(Str::random(4)) . '-' . str_replace('-', '', $student->stud_id);

        $record = [
            'studentID' => $student->stud_id,
            'schlyear' => $term->schlyear,
            'semester' => $term->semester,
            'campus' => $class->campus,
            'course' => $class->course,
            'progCod' => $class->progCode,
            'studYear' => $class->studYear,
            'studSec' => $class->studSec,
            'studClassID' => $class->id,
            'studUnit' => $fees['totals']['units'],
            'postedDate' => $today,
        ] + $choices;

        // Locks: the subjects and section (no two enrollments take the last seat) and the student (no double save)
        DB::connection('schedule')->transaction(fn () => DB::connection('enrollment')->transaction(fn () => DB::connection('assessment')->transaction(function () use ($student, $term, $class, $subjects, $fees, $record, $user, $today, $encode) {
            SubjectOffered::whereIn('id', $subjects->pluck('id'))->lockForUpdate()->get();
            ClassEnroll::whereKey($class->id)->lockForUpdate()->first();
            Student::whereKey($student->id)->lockForUpdate()->first();

            if ($this->isEnrolled($student, $term)) {
                $this->failEnrollment("Student ID Number {$student->stud_id} is already enrolled in {$term->label}.");
            }
            $this->ensureSeats($class, $term, $subjects);

            StudEnrolmentHistory::create($record + ['postedBy' => $user->id, 'status' => self::ENROLLED_STATUS]);
            StudHisLog::create($record + ['postedBy' => trim($user->fname . ' ' . $user->lname), 'encode' => $encode]);

            foreach ($subjects as $subject) {
                $subjectRecord = [
                    'studID' => $student->stud_id,
                    'subjID' => $subject->id,
                    'postedBy' => $user->id,
                    'schlyear' => $term->schlyear,
                    'semester' => $term->semester,
                    'campus' => $class->campus,
                ];
                Grade::create($subjectRecord);
                StudSubLog::create($subjectRecord + ['encode' => $encode]);
            }

            foreach (array_filter($fees['lines'], fn ($line) => $line['saved']) as $line) {
                StudentAppraisal::create([
                    'studID' => $student->stud_id,
                    'schlyear' => $term->schlyear,
                    'semester' => $term->semester,
                    'campus' => $class->campus,
                    'fundID' => $line['fund'],
                    'account' => $line['account'],
                    'amount' => $line['amount'],
                    'dateAssess' => $today,
                    'postedBy' => $user->id,
                ]);
            }
        })));

        return response()->json(['success' => true, 'message' => "{$student->stud_id} is enrolled in {$class->course} for {$term->label}."]);
    }

    // ---------- Form checks ----------

    /**
     * Checks the form and computes the fees. $forSave also checks the level, scholarship, major / minor
     * and 4P's (Assess only needs what changes the fees).
     */
    private function assessment(Request $request, Student $student, ConfigureCurrent $term, bool $forSave): array
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
                'studLevel' => ['required', Rule::exists(StudentLevel::class, 'id'), Rule::when($this->isGraduateStaff(), Rule::in(self::GRADUATE_LEVELS))],
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

    // The picked class section, which must be one of the user's for this term
    private function classSection(Request $request, ConfigureCurrent $term)
    {
        $request->validate(['class_id' => ['required', 'integer']], [], ['class_id' => 'Course Year & Section']);

        $class = $this->classSections($term)->firstWhere('id', (int) $request->input('class_id'));

        if (! $class) {
            $this->failField('class_id', 'The selected Course Year & Section is not offered in your campus this term.');
        }

        return $class;
    }

    // Section and subjects must still have seats (checked again inside the save's locks)
    private function ensureSeats($class, ConfigureCurrent $term, $subjects): void
    {
        $capacity = $this->sectionCapacity($class, $term);
        if ($capacity['isFull']) {
            $this->failField('class_id', "{$class->course} is full ({$capacity['classNo']} students).");
        }

        $taken = Grade::whereIn('subjID', $subjects->pluck('id'))->groupBy('subjID')->selectRaw('subjID, COUNT(*) as total')->pluck('total', 'subjID');
        $full = $subjects->filter(fn ($subject) => ($taken[$subject->id] ?? 0) >= (int) $subject->maxstud);

        if ($full->isNotEmpty()) {
            throw ValidationException::withMessages([
                'subject_ids' => $full->map(fn ($subject) => "{$subject->sub_name} - {$subject->subSec} is full ({$subject->maxstud} students).")->values()->all(),
            ]);
        }
    }

    // Students already enrolled in the section vs its capacity (class_enroll.classno)
    private function sectionCapacity($class, ConfigureCurrent $term): array
    {
        $enrolled = StudEnrolmentHistory::where('schlyear', $term->schlyear)
            ->where('semester', $term->semester)
            ->where('campus', $class->campus)
            ->where('progCod', $class->progCode)
            ->where('studYear', $class->studYear)
            ->where('studSec', $class->studSec)
            ->count();

        return ['enrolled' => $enrolled, 'classNo' => (int) $class->classno, 'isFull' => $enrolled >= (int) $class->classno];
    }

    private function failField(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }

    // ---------- Lists ----------

    // Course Year & Section options of the term and the user's campus (Graduate School staff: GSS programs only)
    private function classSections(ConfigureCurrent $term)
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
    private function offeredSubjects(ConfigureCurrent $term)
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
    private function subjectRow($subject): array
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
