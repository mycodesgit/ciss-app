<?php

namespace App\Http\Controllers\Employee\Enrollment\Concerns;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * A saved enrollment searched by campus, Student ID, school year and semester (Edit Dup. Fees, Print Student RF).
 * Administrators can search every campus, everyone else only their own. Needs ChecksEnrollment.
 */
trait FindsEnrollment
{
    /**
     * The student, term and enrollment of the request (campus, stud_id, schlyear, semester):
     * the campus must be one the user may search, and the student must be enrolled there in that term.
     * Otherwise a validation error (422) with the reason.
     */
    protected function enrollmentOf(Request $request): array
    {
        $request->merge(['stud_id' => strtoupper(trim((string) $request->input('stud_id')))]);

        $data = $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'stud_id' => ['required', 'regex:' . $this->studentIdPattern],
            'schlyear' => ['required', 'string'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ], ['stud_id.regex' => 'The Student ID Number must look like 2024-0001-K.'], ['stud_id' => 'Student ID Number']);

        $student = Student::where('stud_id', $data['stud_id'])->whereCampusIn('campus', [$data['campus']])->first();
        if (! $student) {
            $this->failEnrollment("Student ID Number {$data['stud_id']} does not exist in this campus.");
        }

        $term = ConfigureCurrent::where('schlyear', $data['schlyear'])->where('semester', $data['semester'])->first()
            ?? new ConfigureCurrent(['schlyear' => $data['schlyear'], 'semester' => (int) $data['semester']]);

        $enrollment = StudEnrolmentHistory::where('studentID', $student->stud_id)
            ->where('schlyear', $term->schlyear)
            ->where('semester', $term->semester)
            ->whereCampusIn('campus', [$data['campus']])
            ->first();

        if (! $enrollment) {
            $this->failEnrollment("Student ID Number {$student->stud_id} is not enrolled in {$term->label}.");
        }

        return [$student, $term, $enrollment];
    }

    // Administrators: every campus; everyone else: their own campus(es)
    protected function searchableCampuses(): array
    {
        $campuses = config('ciss.campuses');

        return $this->user()->isAdministrator() ? $campuses : array_intersect_key($campuses, array_flip($this->campuses()));
    }

    // Search form options: campuses, school years (newest first) and semesters
    protected function searchOptions(): array
    {
        return [
            'campuses' => $this->searchableCampuses(),
            'userCampus' => $this->user()->campus,
            'schoolYears' => ConfigureCurrent::orderByDesc('id')->pluck('schlyear')->unique()->values(),
            'semesters' => collect(array_keys(ConfigureCurrent::SEMESTERS)),
        ];
    }
}
