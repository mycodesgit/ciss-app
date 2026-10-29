<?php

namespace App\Http\Requests\Employee\Enrollment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Models\EnrollmentDB\StudentCvlStatus;
use App\Models\EnrollmentDB\StudentGnderStatus;

// A student's information (Enrollment > Student Information). The Student ID, campus and date of admission can't change.
class UpdateStudentInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is checked by the route middleware and the controller
    }

    public function rules(): array
    {
        return [
            // Personal
            'fname' => ['required', 'string', 'max:255'],
            'mname' => ['nullable', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'ext' => ['nullable', 'string', 'max:10'],
            'gender' => ['nullable', Rule::exists(StudentGnderStatus::class, 'genderstat_name')],
            'civil_status' => ['nullable', Rule::exists(StudentCvlStatus::class, 'cvlstat_name')],
            'bday' => ['nullable', 'date', 'before:today'],
            'pbirth' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'religion' => ['nullable', 'string', 'max:255'],
            // Address parts are saved as names (same as the apply form); address is the combined line
            'region' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'brgy' => ['nullable', 'string', 'max:255'],
            'hnum' => ['nullable', 'string', 'max:255'],
            'zcode' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            // Family
            'stud_father' => ['nullable', 'string', 'max:255'],
            'stud_mother' => ['nullable', 'string', 'max:255'],
            'stud_guardian' => ['nullable', 'string', 'max:255'],
            'monthly_income' => ['nullable', 'string', 'max:255'],
            'guardian_contact' => ['nullable', 'string', 'max:20'],
            // Other
            'lstsch_attended' => ['nullable', 'string', 'max:255'],
            'lst_sch_attended_year' => ['nullable', 'string', 'max:20'],
            'suc_lst_attended' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'fname' => 'first name',
            'mname' => 'middle name',
            'lname' => 'last name',
            'ext' => 'name extension',
            'civil_status' => 'civil status',
            'bday' => 'birthday',
            'pbirth' => 'birth place',
            'contact' => 'mobile no.',
            'brgy' => 'barangay',
            'hnum' => 'house no.',
            'zcode' => 'zip code',
            'stud_father' => "father's name",
            'stud_mother' => "mother's name",
            'stud_guardian' => "guardian's name",
            'monthly_income' => 'monthly income',
            'guardian_contact' => 'guardian contact no.',
            'lstsch_attended' => 'last school attended',
            'lst_sch_attended_year' => 'last S.Y. attended',
            'suc_lst_attended' => 'last university attended',
        ];
    }
}
