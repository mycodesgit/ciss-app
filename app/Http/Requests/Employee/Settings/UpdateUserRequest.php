<?php

namespace App\Http\Requests\Employee\Settings;

use Illuminate\Validation\Rule;

class UpdateUserRequest extends StoreUserRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // Password has its own action; email must stay unique except for this user
        unset($rules['password']);
        $rules['mname'] = ['nullable', 'string', 'max:255'];
        $rules['email'] = ['required', 'string', 'email', 'max:191', Rule::unique('users', 'email')->ignore($this->route('user'))];

        return $rules;
    }
}
