<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class EmployeeLoginController extends Controller
{
    public function index()
    {
        return view('auth.emplogin');
    }
}
