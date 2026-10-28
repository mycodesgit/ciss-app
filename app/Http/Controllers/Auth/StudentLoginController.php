<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class StudentLoginController extends Controller
{
    public function index()
    {
        return view('auth.studlogin');
    }
}
