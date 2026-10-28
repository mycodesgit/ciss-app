<?php

namespace App\Http\Requests\Employee\Settings;

use App\Models\SettingDB\ConfigureCurrent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Add / edit an A.Y. & Semester (settings_conf). Each school year + semester pair is unique.
 */
class AcademicTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is checked by the route middleware
    }

    public function rules(): array
    {
        return [
            'schlyear' => [
                'required',
                'regex:/^\d{4}-\d{4}$/',
                Rule::unique('settings.settings_conf', 'schlyear')
                    ->where('semester', $this->input('semester'))
                    ->ignore($this->route('academicTerm')),
            ],
            'semester' => ['required', 'integer', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
            'set_status' => ['required', 'integer', Rule::in(array_keys(ConfigureCurrent::STATUSES))],
        ];
    }

    public function attributes(): array
    {
        return [
            'schlyear' => 'school year',
            'set_status' => 'status',
        ];
    }

    public function messages(): array
    {
        return [
            'schlyear.regex' => 'The school year must look like 2025-2026.',
            'schlyear.unique' => 'This school year and semester already exist.',
        ];
    }
}
