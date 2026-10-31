<?php

namespace App\Http\Controllers\Employee\Scheduling;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\ScheduleDB\College;
use App\Support\CampusList;

/**
 * Scheduling > Colleges (administrators): the academic colleges (config ciss.scheduling.college_ids) and the
 * campuses each one is in. Only the campuses can be changed. (scheduling/colleges.js)
 */
class CollegeController extends Controller
{
    public function index()
    {
        return view('pages.employee.scheduling.colleges.index', ['campuses' => config('ciss.campuses')]);
    }

    public function list()
    {
        $colleges = College::whereIn('id', config('ciss.scheduling.college_ids'))->orderBy('college_name')->get();

        return response()->json([
            'data' => $colleges->map(fn (College $college) => [
                'id' => $college->id,
                'college_abbr' => $college->college_abbr,
                'college_name' => $college->college_name,
                'campuses' => CampusList::split($college->campus),
            ]),
        ]);
    }

    public function update(Request $request, College $college)
    {
        abort_unless(in_array($college->id, config('ciss.scheduling.college_ids')), 404);

        $campuses = $request->validate([
            'campus' => ['required', 'array', 'min:1'],
            'campus.*' => ['distinct', Rule::in(array_keys(config('ciss.campuses')))],
        ], ['campus.required' => 'Pick at least one campus.'])['campus'];

        $college->update(['campus' => implode(',', $campuses)]);

        return response()->json(['success' => true, 'message' => "Campuses of {$college->college_abbr} saved."]);
    }
}
