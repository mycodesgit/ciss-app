<?php

namespace App\Http\Controllers\Employee\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Enrollment\Concerns\BuildsEnrollmentForm;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

use App\Models\EnrollmentDB\Grade;
use App\Models\EnrollmentDB\MajorMinor;
use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Models\EnrollmentDB\StudHisLog;
use App\Models\EnrollmentDB\StudSubLog;
use App\Models\ScheduleDB\ClassEnroll;
use App\Models\ScheduleDB\SubjectOffered;

/**
 * Enrollment > Enroll Student, the enrollment form of one student for one open term.
 *  - create():          the form (opened from the search; a direct URL goes through the same checks)
 *  - sectionSubjects(): AJAX, picking a Course Year & Section: its capacity and its template subjects
 *  - assess():          AJAX, the fee assessment of the chosen section, subjects and student status / type
 *  - store():           saves the enrollment
 * The form's rules are in Concerns/BuildsEnrollmentForm (shared with Edit Enrollment). (enroll-form.js)
 */
class EnrollmentFormController extends Controller
{
    use ChecksEnrollment, BuildsEnrollmentForm;

    private const ENROLLED_STATUS = 2;          // program_en_history.status of an enrollment saved here
    private const DEFAULT_LEVEL = 50;
    private const DEFAULT_MAJOR_MINOR = 43;     // majorminor_subject.id selected by default
    private const DEFAULT_TRANSFEREE = 3;

    public function create(Request $request, string $student)
    {
        try {
            [$student, $term] = $this->enrollableStudent($request->merge(['stud_id' => $student]));
        } catch (ValidationException $e) {
            return redirect()->route('employee.enrollment.enroll.index')->with('error', collect($e->errors())->flatten()->first());
        }

        $majorMinor = MajorMinor::whereKey(self::DEFAULT_MAJOR_MINOR)->value('submamiID');

        return view('pages.employee.enrollment.studenroll.enrollstudent', $this->formOptions($term) + [
            'student' => $student,
            'term' => $term,
            // Defaults: the scholarship of the student's last enrollment, and the old form's preselected values
            'selected' => [
                'class_id' => null,
                'studLevel' => $this->isGraduateStaff() ? null : self::DEFAULT_LEVEL,
                'studSch' => StudEnrolmentHistory::where('studentID', $student->stud_id)->orderByDesc('id')->value('studSch'),
                'studMajor' => $majorMinor,
                'studMinor' => $majorMinor,
                'studStatus' => null,
                'studType' => null,
                'transferee' => self::DEFAULT_TRANSFEREE,
                'fourPs' => null,
            ],
            'classInfo' => [],
        ]);
    }

    public function sectionSubjects(Request $request, string $student)
    {
        [, $term] = $this->enrollableStudent($request->merge(['stud_id' => $student]));

        return $this->sectionSubjectsResponse($request, $term);
    }

    public function assess(Request $request, string $student)
    {
        [$student, $term] = $this->enrollableStudent($request->merge(['stud_id' => $student]));

        return response()->json($this->enrollmentInput($request, $student, $term, false)['fees']);
    }

    public function store(Request $request, string $student)
    {
        [$student, $term] = $this->enrollableStudent($request->merge(['stud_id' => $student]));
        $input = $this->enrollmentInput($request, $student, $term, true);
        $record = $this->enrollmentRecord($student, $term, $input);

        $user = $this->user();
        $encode = now()->format('Ymd') . '-' . strtoupper(Str::random(4)) . '-' . str_replace('-', '', $student->stud_id);

        // Locks: the subjects and section (no two enrollments take the last seat) and the student (no double save)
        $this->inEnrollmentTransaction(function () use ($student, $term, $input, $record, $user, $encode) {
            SubjectOffered::whereIn('id', $input['subjects']->pluck('id'))->lockForUpdate()->get();
            ClassEnroll::whereKey($input['class']->id)->lockForUpdate()->first();
            Student::whereKey($student->id)->lockForUpdate()->first();

            if ($this->isEnrolled($student, $term)) {
                $this->failEnrollment("Student ID Number {$student->stud_id} is already enrolled in {$term->label}.");
            }
            $this->ensureSeats($input['class'], $term, $input['subjects']);

            StudEnrolmentHistory::create($record + ['postedBy' => $user->id, 'status' => self::ENROLLED_STATUS]);
            StudHisLog::create($record + ['postedBy' => trim($user->fname . ' ' . $user->lname), 'encode' => $encode]);

            foreach ($input['subjects'] as $subject) {
                $subjectRecord = [
                    'studID' => $student->stud_id,
                    'subjID' => $subject->id,
                    'postedBy' => $user->id,
                    'schlyear' => $term->schlyear,
                    'semester' => $term->semester,
                    'campus' => $input['class']->campus,
                ];
                Grade::create($subjectRecord);
                StudSubLog::create($subjectRecord + ['encode' => $encode]);
            }

            $this->saveAppraisal($student, $term, $input['class']->campus, $input['fees']);
        });

        return response()->json(['success' => true, 'message' => "{$student->stud_id} is enrolled in {$input['class']->course} for {$term->label}."]);
    }
}
