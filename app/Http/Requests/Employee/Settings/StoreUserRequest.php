<?php

namespace App\Http\Requests\Employee\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public const NAME_EXTENSIONS = ['Jr.', 'Sr.', 'III', 'IV'];

    public function authorize(): bool
    {
        return true; // access is checked by the route middleware
    }

    public function rules(): array
    {
        return [
            'fname' => ['required', 'string', 'max:255'],
            'mname' => ['required', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'ext' => ['nullable', Rule::in(self::NAME_EXTENSIONS)],
            'email' => ['required', 'string', 'email', 'max:191', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'campus' => ['required', Rule::in(array_keys(config('ciss.campuses')))],
            'dept' => ['required', Rule::in(array_keys(config('ciss.departments')))],
            'role' => ['required', 'integer', Rule::in(array_keys(config('ciss.roles')))],
        ];
    }

    public function attributes(): array
    {
        return [
            'fname' => 'first name',
            'mname' => 'middle name',
            'lname' => 'last name',
            'ext' => 'name extension',
            'dept' => 'department',
            'role' => 'user level',
        ];
    }
}
