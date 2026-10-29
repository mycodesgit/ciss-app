<?php

namespace App\Http\Controllers\Employee\Enrollment\Concerns;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\EnrollmentDB\EncodedGrade;
use App\Models\EnrollmentDB\Grade;
use App\Models\ScheduleDB\SubjectOffered;
use App\Models\SettingDB\ConfigureCurrent;
use App\Services\Enrollment\GradeRules;

/**
 * Shared by Gradesheet and Correction of Grades: the subjects a user may grade, a subject's grade rows, the
 * allowed grade codes and the studgrades_logs entry of every save. Uses ChecksEnrollment (user, campuses, role).
 *
 * studgrades.status   (final grade): empty = none, 1 = encoded (editable), 2 = submitted (locked)
 * studgrades.compstat (completion):  empty = none, 1 = encoded (editable), 2 = submitted (locked)
 */
trait ReadsGradesheet
{
    protected int $encodedStatus = 1;
    protected int $submittedStatus = 2;

    // Subjects the user may grade: offered in the user's campus; Graduate School staff only GSS subjects, others the rest
    protected function allowedSubjects()
    {
        return SubjectOffered::join('subjects', 'sub_offered.subCode', '=', 'subjects.sub_code')
            ->whereIn('sub_offered.campus', $this->campuses())
            ->when(
                $this->isGraduateStaff(),
                fn ($query) => $query->where('subjects.subjdep', 'LIKE', '%GSS'),
                fn ($query) => $query->where(fn ($where) => $where->where('subjects.subjdep', 'NOT LIKE', '%GSS')->orWhereNull('subjects.subjdep')),
            );
    }

    // Subjects of a term for the subjects table; $showRoute is the gradesheet / correction page of a subject
    protected function subjectsResponse(Request $request, string $showRoute)
    {
        $data = $request->validate([
            'schlyear' => ['nullable', 'string'],
            'semester' => ['nullable', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ]);

        // Nothing until a term is picked (the page's first load)
        if (empty($data['schlyear']) || empty($data['semester'])) {
            return response()->json(['data' => []]);
        }

        $subjects = $this->allowedSubjects()
            ->where('sub_offered.schlyear', $data['schlyear'])
            ->where('sub_offered.semester', $data['semester'])
            ->orderBy('subjects.sub_name')->orderBy('sub_offered.subSec')
            ->get(['sub_offered.id', 'subjects.sub_name', 'subjects.sub_title', 'sub_offered.subSec']);

        return response()->json([
            'data' => $subjects->map(fn ($subject) => $subject->only(['id', 'sub_name', 'sub_title', 'subSec']) + [
                'url' => route($showRoute, $subject->id),
            ]),
        ]);
    }

    protected function subject(int $id)
    {
        $subject = $this->allowedSubjects()
            ->where('sub_offered.id', $id)
            ->first(['sub_offered.id', 'sub_offered.subCode', 'sub_offered.subSec', 'sub_offered.subUnit', 'sub_offered.campus',
                'sub_offered.schlyear', 'sub_offered.semester', 'subjects.sub_name', 'subjects.sub_title']);

        abort_unless($subject, 404, 'Subject not found in your campus.');

        return $subject;
    }

    protected function termOf($subject): ConfigureCurrent
    {
        return ConfigureCurrent::where('schlyear', $subject->schlyear)->where('semester', $subject->semester)->first()
            ?? new ConfigureCurrent(['schlyear' => $subject->schlyear, 'semester' => (int) $subject->semester]);
    }

    // A studgrades row, reached through its subject (so only rows of the user's allowed subjects)
    protected function gradeRow(int $id): array
    {
        $row = Grade::findOrFail($id);

        return [$row, $this->subject((int) $row->subjID)];
    }

    // Empty clears the grade; anything else must be one of the codes the user may encode
    protected function validatedGrade(Request $request): ?string
    {
        $data = $request->validate([
            'grade' => ['nullable', 'string', Rule::in(GradeRules::codesFor($this->user())->all())],
        ], ['grade.in' => 'This grade is not allowed.']);

        return ($data['grade'] ?? '') === '' ? null : $data['grade'];
    }

    protected function gradeRows($subject)
    {
        return Grade::join('students', 'studgrades.studID', '=', 'students.stud_id')
            ->where('studgrades.subjID', $subject->id)
            ->orderBy('students.lname')->orderBy('students.fname')
            ->get(['studgrades.id', 'studgrades.studID', 'studgrades.subjFgrade', 'studgrades.subjComp', 'studgrades.status',
                'studgrades.compstat', 'studgrades.creditEarned', 'students.lname', 'students.fname', 'students.mname']);
    }

    // The columns every gradesheet row shows
    protected function rowBasics($row): array
    {
        $shown = $row->subjComp ?: $row->subjFgrade;

        return [
            'id' => $row->id,
            'studID' => $row->studID,
            'name' => trim($row->lname . ', ' . $row->fname . ' ' . ($row->mname ? mb_strtoupper(mb_substr($row->mname, 0, 1)) . '.' : '')),
            'grade' => $row->subjFgrade,
            'completion' => $row->subjComp,
            'equivalent' => GradeRules::equivalent($shown, $row->studID),
            'equivalentWarning' => ! $row->subjComp && GradeRules::isWarning($row->subjFgrade),
            'gradeWarning' => GradeRules::isWarning($row->subjFgrade),
            'credit' => $row->creditEarned,
            'submitted' => (int) $row->status === $this->submittedStatus,
        ];
    }

    // studgrades_logs: one row per save (who, when, the grade)
    protected function logGrade($row, array $values): void
    {
        $user = $this->user();

        EncodedGrade::create([
            'grdeprimID' => $row->id,
            'studsID' => $row->studID,
            'subjctsID' => $row->subjID,
            'campus' => $row->campus,
            'encodedBy' => trim($user->fname . ' ' . $user->lname),
        ] + $values);
    }

    protected function failGrade(string $message): never
    {
        throw ValidationException::withMessages(['grade' => $message]);
    }
}
