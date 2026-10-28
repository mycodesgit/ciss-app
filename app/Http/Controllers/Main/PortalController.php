<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;

class PortalController extends Controller
{
    public function home()
    {
        return view('main');
    }

    public function admission()
    {
        return view('pages.portal.index');
    }
}
