<?php

namespace App\Http\Requests\Employee\Admission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Personal information of an applicant (List of Applicant > View Data)
class UpdateApplicantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is checked by the route middleware and the controller
    }

    public function rules(): array
    {
        return [
            'fname' => ['required', 'string', 'max:255'],
            'mname' => ['nullable', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'ext' => ['nullable', Rule::in(['Jr.', 'Sr.', 'III', 'IV'])],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'bday' => ['required', 'date', 'before:today'],
            'civil_status' => ['required', Rule::in(['Single', 'Married', 'Divorced', 'Widowed', 'Separated'])],
            'address' => ['required', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'fname' => 'first name',
            'mname' => 'middle name',
            'lname' => 'last name',
            'ext' => 'name extension',
            'bday' => 'birthday',
        ];
    }
}
