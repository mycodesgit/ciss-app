<?php

namespace App\Http\Controllers\Employee\Enrollment\Concerns;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\ScheduleDB\ClassEnroll;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Search by campus, school year, semester and course (EL and PL, Ranking).
 * Graduate School staff see graduate school programs only. Needs ChecksEnrollment and FindsEnrollment.
 */
trait SearchesCourses
{
    private string $graduatePrograms = '-GSS-'; // part of the progCod of graduate school programs

    // AJAX: the programs with classes in the campus and term (Course select)
    public function courses(Request $request)
    {
        $data = $this->validateTerm($request);
        $programs = config('database.connections.schedule.database') . '.programs';

        $courses = ClassEnroll::join("{$programs} as programs", 'class_enroll.progCode', '=', 'programs.progCod')
            ->where('class_enroll.schlyear', $data['schlyear'])
            ->where('class_enroll.semester', $data['semester'])
            ->whereCampusIn('class_enroll.campus', [$data['campus']])
            ->when($this->isGraduateStaff(), fn ($query) => $query->where('class_enroll.progCode', 'like', "%{$this->graduatePrograms}%"))
            ->distinct()
            ->orderBy('programs.progAcronym')
            ->get(['programs.progCod', 'programs.progAcronym']);

        return response()->json(['data' => $courses]);
    }

    protected function validateTerm(Request $request): array
    {
        return $request->validate([
            'campus' => ['required', Rule::in(array_keys($this->searchableCampuses()))],
            'schlyear' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ]);
    }

    // Campus, term and course of the request; null when the user may not see the course (Graduate School staff)
    protected function validateCourse(Request $request): ?array
    {
        $data = $this->validateTerm($request) + $request->validate(['progCod' => ['required', 'string', 'max:50']]);

        return $this->isGraduateStaff() && ! str_contains($data['progCod'], $this->graduatePrograms) ? null : $data;
    }
}
