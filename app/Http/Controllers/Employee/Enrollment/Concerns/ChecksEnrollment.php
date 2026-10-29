<?php

namespace App\Http\Controllers\Employee\Enrollment\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\EnrollmentDB\StudEnrolmentHistory;
use App\Models\EnrollmentDB\Student;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Who can be enrolled, shared by the Enroll Student search and the enrollment form:
 *  - the term (schlyear + semester) is one of the open (active) terms
 *  - the student exists in the user's campus
 *  - the student has no enrollment yet in that term (any campus)
 */
trait ChecksEnrollment
{
    // Student ID format: 2024-0001-K
    protected string $studentIdPattern = '/^\d{4}-\d{4}-[A-Z]$/';

    // Graduate School staff enroll only graduate school classes and levels
    protected int $graduateRole = 15;

    /**
     * The student and term of the request (stud_id, schlyear, semester) when the student can be enrolled,
     * otherwise a validation error (422) with the reason.
     */
    protected function enrollableStudent(Request $request): array
    {
        $request->merge(['stud_id' => strtoupper(trim((string) $request->input('stud_id')))]);

        $data = $request->validate([
            'stud_id' => ['required', 'regex:' . $this->studentIdPattern],
            'schlyear' => ['required', 'string'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ], ['stud_id.regex' => 'The Student ID Number must look like 2024-0001-K.'], ['stud_id' => 'Student ID Number']);

        $term = $this->openTerms()
            ->first(fn (ConfigureCurrent $term) => $term->schlyear === $data['schlyear'] && (int) $term->semester === (int) $data['semester']);

        if (! $term) {
            $this->failEnrollment('Enrollment is not open for the selected school year and semester.');
        }

        $student = Student::where('stud_id', $data['stud_id'])->whereIn('campus', $this->campuses())->first();

        if (! $student) {
            $this->failEnrollment("Student ID Number {$data['stud_id']} does not exist in your campus.");
        }

        if ($this->isEnrolled($student, $term)) {
            $this->failEnrollment("Student ID Number {$student->stud_id} is already enrolled in {$term->label}.");
        }

        return [$student, $term];
    }

    protected function isEnrolled(Student $student, ConfigureCurrent $term): bool
    {
        return StudEnrolmentHistory::where('studentID', $student->stud_id)
            ->where('schlyear', $term->schlyear)
            ->where('semester', $term->semester)
            ->exists();
    }

    protected function failEnrollment(string $message): never
    {
        throw ValidationException::withMessages(['stud_id' => $message]);
    }

    // Terms open for enrollment: the active ones (settings_conf.set_status = 2), newest first
    protected function openTerms()
    {
        return ConfigureCurrent::where('set_status', ConfigureCurrent::STATUS_ACTIVE)
            ->orderByDesc('id')
            ->get(['id', 'schlyear', 'semester', 'set_status']);
    }

    protected function user()
    {
        return Auth::guard('web')->user();
    }

    protected function isGraduateStaff(): bool
    {
        return (int) $this->user()->role === $this->graduateRole;
    }

    // The user's campus code(s): users.campus can hold a list such as "MC, VC"
    protected function campuses(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->user()->campus))));
    }
}
