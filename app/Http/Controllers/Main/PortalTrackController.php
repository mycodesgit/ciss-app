<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;

class PortalTrackController extends Controller
{
    // The search itself runs in the Livewire component App\Livewire\Portal\TrackAdmission,
    // so results show up without a page reload
    public function index()
    {
        return view('pages.portal.track');
    }
}
