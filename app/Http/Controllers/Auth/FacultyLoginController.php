<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class FacultyLoginController extends Controller
{
    public function index()
    {
        return view('auth.faclogin');
    }
}
