<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\BuildsEnrollmentForm;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\ShowsQueuePanel;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\AdmissionDB\User;
use App\Models\AssessmentDB\StudentAppraisal;
use App\Models\AssessmentDB\StudPayment;
use App\Models\EnrollmentDB\DeleteEnrollmentLogs;
use App\Models\EnrollmentDB\Grade;
use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Models\EnrollmentDB\StudHisLog;
use App\Models\EnrollmentDB\StudSubLog;
use App\Models\ScheduleDB\ClassEnroll;
use App\Models\ScheduleDB\Faculty;
use App\Models\ScheduleDB\SubjectOffered;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Enrollment > Edit Enrollment: change, print or delete an existing enrollment in the user's campus.
 *  - index() / findEnrollment(): search page (Student ID + school year + semester), queue panel, pre-enrolled list
 *  - edit():                     the enrollment form filled with the saved enrollment
 *  - sectionSubjects() / assess(): like Enroll Student
 *  - update():                   saves the changes (subjects added / removed, fees re-assessed)
 *  - donePrint():                "For Printing" (status 3) -> enrolled (2)
 *  - destroy():                  deletes the enrollment (administrators only; logged in delete_enrollment_logs)
 * School years: the active ones plus the user's year access (Accounts > User's Management).
 * The form's rules are in Concerns/BuildsEnrollmentForm. (enroll.js, enroll-form.js)
 */
class EditEnrollmentController extends Controller
{
    use ChecksEnrollment, BuildsEnrollmentForm, ShowsQueuePanel;

    private const PRE_ENROLLED = 1;     // posted by a faculty (pre-enrollment)
    private const ENROLLED = 2;
    private const FOR_PRINTING = 3;
    private const PRINTABLE_STATUSES = [self::ENROLLED, self::FOR_PRINTING];

    public function index()
    {
        return view('pages.employee.enrollment.studenroll.editenroll', $this->queuePanelData() + [
            'schoolYears' => $this->editableYears(),
            'semesters' => collect(array_keys(ConfigureCurrent::SEMESTERS)),
        ]);
    }

    public function findEnrollment(Request $request)
    {
        [$student, $term] = $this->editableEnrollment($request);

        return response()->json(['url' => $this->editUrl($student->stud_id, $term)]);
    }

    public function edit(Request $request, string $student)
    {
        try {
            [$student, $term, $enrollment] = $this->editableEnrollment($request->merge(['stud_id' => $student]));
        } catch (ValidationException $e) {
            return redirect()->route('employee.enrollment.edit.index')->with('error', collect($e->errors())->flatten()->first());
        }

        $options = $this->formOptions($term);
        $class = $options['classSections']->firstWhere('id', (int) $enrollment->studClassID);

        return view('pages.employee.enrollment.studenroll.editenrollstudent', $options + [
            'student' => $student,
            'term' => $term,
            'enrollment' => $enrollment,
            'selected' => [
                'class_id' => $enrollment->studClassID,
                'studLevel' => $enrollment->studLevel,
                'studSch' => $enrollment->studSch,
                'studMajor' => $enrollment->studMajor,
                'studMinor' => $enrollment->studMinor,
                'studStatus' => $enrollment->studStatus,
                'studType' => $enrollment->studType,
                'transferee' => $enrollment->transferee,
                'fourPs' => (string) ($enrollment->fourPs ?? '0'),
            ],
            'classInfo' => ['programName' => $class?->progName, 'yearLevel' => $class?->yearleveldesc],
            'currentSubjects' => $this->currentSubjects($student, $term)->map(fn ($subject) => $this->subjectRow($subject))->values(),
            'savedFees' => StudentAppraisal::where('studID', $student->stud_id)
                ->where('schlyear', $term->schlyear)
                ->where('semester', $term->semester)
                ->where('campus', $enrollment->campus)
                ->orderBy('account')
                ->get(['fundID', 'account', 'amount']),
            'postedByName' => $this->postedByName($enrollment),
            'canPrint' => in_array((int) $enrollment->status, self::PRINTABLE_STATUSES, true),
            'isForPrinting' => (int) $enrollment->status === self::FOR_PRINTING,
            'canDelete' => $this->user()->isAdministrator(),
        ]);
    }

    public function sectionSubjects(Request $request, string $student)
    {
        [, $term, $enrollment] = $this->editableEnrollment($request->merge(['stud_id' => $student]));

        return $this->sectionSubjectsResponse($request, $term, $enrollment);
    }

    public function assess(Request $request, string $student)
    {
        [$student, $term] = $this->editableEnrollment($request->merge(['stud_id' => $student]));

        return response()->json($this->enrollmentInput($request, $student, $term, false)['fees']);
    }

    public function update(Request $request, string $student)
    {
        [$student, $term, $enrollment] = $this->editableEnrollment($request->merge(['stud_id' => $student]));
        $input = $this->enrollmentInput($request, $student, $term, true);
        $record = $this->enrollmentRecord($student, $term, $input);

        $user = $this->user();
        $encode = now()->format('Ymd') . '-' . strtoupper(Str::random(4)) . '-' . str_replace('-', '', $student->stud_id);

        $this->inEnrollmentTransaction(function () use ($student, $term, $enrollment, $input, $record, $user, $encode) {
            // Locks: the subjects and section (seats) and the enrollment (no two saves at once)
            SubjectOffered::whereIn('id', $input['subjects']->pluck('id'))->lockForUpdate()->get();
            ClassEnroll::whereKey($input['class']->id)->lockForUpdate()->first();
            StudEnrolmentHistory::whereKey($enrollment->id)->lockForUpdate()->first();

            $keptIds = $this->currentSubjects($student, $term)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $newIds = $input['subjects']->pluck('id')->map(fn ($id) => (int) $id)->all();
            $removedIds = array_values(array_diff($keptIds, $newIds));
            $addedIds = array_values(array_diff($newIds, $keptIds));

            $this->ensureSeats($input['class'], $term, $input['subjects'], $enrollment, $keptIds);
            $this->ensureNoGrades($student, $removedIds, 'Subjects with a grade can\'t be removed');

            Grade::where('studID', $student->stud_id)->whereIn('subjID', $removedIds)->delete();

            foreach ($addedIds as $subjectId) {
                $subjectRecord = [
                    'studID' => $student->stud_id,
                    'subjID' => $subjectId,
                    'postedBy' => $user->id,
                    'schlyear' => $term->schlyear,
                    'semester' => $term->semester,
                    'campus' => $input['class']->campus,
                ];
                Grade::create($subjectRecord);
                StudSubLog::create($subjectRecord + ['encode' => $encode]);
            }

            $previousCampus = $enrollment->campus;
            $enrollment->update($record + ['postedBy' => $user->id, 'status' => self::ENROLLED]);
            StudHisLog::create($record + ['postedBy' => trim($user->fname . ' ' . $user->lname), 'encode' => $encode]);

            $this->saveAppraisal($student, $term, $input['class']->campus, $input['fees'], $previousCampus);
        });

        return response()->json(['success' => true, 'message' => "The enrollment of {$student->stud_id} for {$term->label} is updated."]);
    }

    public function donePrint(Request $request, string $student)
    {
        [$student, $term, $enrollment] = $this->editableEnrollment($request->merge(['stud_id' => $student]));

        if ((int) $enrollment->status !== self::FOR_PRINTING) {
            $this->failEnrollment('This enrollment is not waiting for printing.');
        }

        $enrollment->update(['status' => self::ENROLLED]);

        return response()->json(['success' => true, 'message' => "Student RF of {$student->stud_id} is done printing."]);
    }

    // Administrators only
    public function destroy(Request $request, string $student)
    {
        $user = $this->user();
        abort_unless($user->isAdministrator(), 403, 'Only administrators can delete an enrollment.');

        [$student, $term, $enrollment] = $this->editableEnrollment($request->merge(['stud_id' => $student]));

        $this->inEnrollmentTransaction(function () use ($student, $term, $enrollment, $user) {
            StudEnrolmentHistory::whereKey($enrollment->id)->lockForUpdate()->first();

            $subjectIds = $this->currentSubjects($student, $term)->pluck('id')->all();

            // Grades and payments are records of their own: they are never deleted with the enrollment
            $this->ensureNoGrades($student, $subjectIds, 'This enrollment has subjects with a grade, so it can\'t be deleted');

            $hasPayments = StudPayment::where('studID', $student->stud_id)
                ->where('schlyear', $term->schlyear)
                ->where('semester', $term->semester)
                ->exists();
            if ($hasPayments) {
                $this->failEnrollment('This enrollment has payments, so it can\'t be deleted. Please contact the Cashier / Assessment Office.');
            }

            DeleteEnrollmentLogs::create([
                'delstudentID' => $student->stud_id,
                'delMC' => $enrollment->campus,
                'delemployeename' => trim($user->fname . ' ' . $user->lname),
                'delsemester' => $term->semester,
                'delschlyear' => $term->schlyear,
            ]);

            Grade::where('studID', $student->stud_id)->whereIn('subjID', $subjectIds)->delete();
            StudentAppraisal::where('studID', $student->stud_id)
                ->where('schlyear', $term->schlyear)
                ->where('semester', $term->semester)
                ->where('campus', $enrollment->campus)
                ->delete();
            $enrollment->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "The enrollment of {$student->stud_id} for {$term->label} is deleted.",
            'redirect' => route('employee.enrollment.edit.index'),
        ]);
    }

    // ---------- Rules ----------

    /**
     * The student, term and enrollment of the request (stud_id, schlyear, semester), when it can be edited:
     *  - the school year is one the user may edit (active years + the user's year access)
     *  - the student exists in the user's campus and is enrolled in that term in the user's campus
     */
    private function editableEnrollment(Request $request): array
    {
        $request->merge(['stud_id' => strtoupper(trim((string) $request->input('stud_id')))]);

        $data = $request->validate([
            'stud_id' => ['required', 'regex:' . $this->studentIdPattern],
            'schlyear' => ['required', Rule::in($this->editableYears()->all())],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ], [
            'stud_id.regex' => 'The Student ID Number must look like 2024-0001-K.',
            'schlyear.in' => 'You have no access to edit enrollments of this school year.',
        ], ['stud_id' => 'Student ID Number']);

        $student = Student::where('stud_id', $data['stud_id'])->whereIn('campus', $this->campuses())->first();
        if (! $student) {
            $this->failEnrollment("Student ID Number {$data['stud_id']} does not exist in your campus.");
        }

        $enrollment = StudEnrolmentHistory::where('studentID', $student->stud_id)
            ->where('schlyear', $data['schlyear'])
            ->where('semester', $data['semester'])
            ->whereIn('campus', $this->campuses())
            ->first();

        $term = ConfigureCurrent::where('schlyear', $data['schlyear'])->where('semester', $data['semester'])->first()
            ?? new ConfigureCurrent(['schlyear' => $data['schlyear'], 'semester' => (int) $data['semester']]);

        if (! $enrollment) {
            $this->failEnrollment("Student ID Number {$student->stud_id} is not enrolled in {$term->label}.");
        }

        return [$student, $term, $enrollment];
    }

    // School years the user may edit: the active ones + the user's year access
    private function editableYears()
    {
        return $this->openTerms()->pluck('schlyear')
            ->merge($this->user()->buttonAccess?->schlyraccess ?? [])
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();
    }

    // The student's subjects of the term (studgrades of subjects offered in the term and the user's campus)
    private function currentSubjects(Student $student, ConfigureCurrent $term)
    {
        $subjectIds = Grade::where('studID', $student->stud_id)->pluck('subjID');

        return $this->offeredSubjects($term)->whereIn('sub_offered.id', $subjectIds)->orderBy('sub_offered.subCode')->get();
    }

    // Subjects that already have a final grade can't be removed / deleted
    private function ensureNoGrades(Student $student, array $subjectIds, string $message): void
    {
        $graded = Grade::where('studID', $student->stud_id)
            ->whereIn('subjID', $subjectIds)
            ->whereNotNull('subjFgrade')
            ->where('subjFgrade', '!=', '')
            ->pluck('subjID');

        if ($graded->isNotEmpty()) {
            $names = SubjectOffered::join('subjects', 'sub_offered.subCode', '=', 'subjects.sub_code')
                ->whereIn('sub_offered.id', $graded)
                ->get(['subjects.sub_name', 'sub_offered.subSec'])
                ->map(fn ($subject) => "{$subject->sub_name} - {$subject->subSec}")
                ->implode(', ');

            $this->failField('subject_ids', "{$message}: {$names}.");
        }
    }

    // Who posted the enrollment: a faculty for a pre-enrollment (status 1), otherwise an employee
    private function postedByName(StudEnrolmentHistory $enrollment): string
    {
        $poster = (int) $enrollment->status === self::PRE_ENROLLED
            ? Faculty::find($enrollment->postedBy)
            : User::find($enrollment->postedBy);

        return $poster ? trim($poster->fname . ' ' . $poster->lname) : '';
    }

    private function editUrl(string $studentId, ConfigureCurrent $term): string
    {
        return route('employee.enrollment.edit.show', ['student' => $studentId, 'schlyear' => $term->schlyear, 'semester' => $term->semester]);
    }
}
