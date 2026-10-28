<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmployeeLoginRequest extends FormRequest
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;
    private const ACTIVE_STATUS = 1; // users.statuser: 1 = active, anything else = disabled

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:191'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Log the employee in on the "web" guard, or fail with a generic message.
     * Failed attempts are counted per email + IP and locked out after MAX_ATTEMPTS.
     * Only active accounts (statuser = 1) can log in.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $guard = Auth::guard('web');

        // Check email + password first, without logging in yet
        if (!$guard->validate($this->only('email', 'password'))) {
            RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);

            // Same message for unknown email and wrong password (no account guessing)
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        $user = $guard->getLastAttempted();

        // Only revealed to someone who already knows the correct password
        if ((int) $user->statuser !== self::ACTIVE_STATUS) {
            throw ValidationException::withMessages([
                'email' => 'Your account is disabled. Please contact the administrator.',
            ]);
        }

        $guard->login($user);
    }

    private function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
        ]);
    }

    private function throttleKey(): string
    {
        return Str::lower($this->string('email')) . '|' . $this->ip();
    }
}
