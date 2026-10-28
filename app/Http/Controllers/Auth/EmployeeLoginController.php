<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmployeeLoginRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeLoginController extends Controller
{
    public function index()
    {
        return view('auth.emplogin');
    }

    public function login(EmployeeLoginRequest $request)
    {
        $request->authenticate();

        // New session id after login (prevents session fixation)
        $request->session()->regenerate();

        return redirect()->intended(route('employee.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employee.login');
    }
}
