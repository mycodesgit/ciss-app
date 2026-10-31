<?php

namespace App\Http\Controllers\Employee\Scheduling\Concerns;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\ScheduleDB\EnPrograms;
use App\Models\SettingDB\ConfigureCurrent;
use App\Support\CampusList;

/**
 * What every Scheduling page works on:
 *  - a campus (?campus=): administrators any campus, everyone else only their own (FindsEnrollment::searchableCampuses)
 *  - a term (schlyear + semester): one configured in Accounts > A.Y. & Semester
 *  - Graduate School Staff see graduate school programs only
 * Saves that must not create duplicates run inside exclusively(): the schedule tables have no unique keys,
 * so two quick clicks would otherwise both pass the "already exists" check. Needs ChecksEnrollment and FindsEnrollment.
 */
trait ScopesScheduling
{
    protected function schedulingCampus(Request $request): string
    {
        $campuses = array_keys($this->searchableCampuses());

        if ($request->filled('campus')) {
            return $request->validate(['campus' => ['required', 'string', Rule::in($campuses)]])['campus'];
        }

        $own = $this->campuses()[0] ?? null;
        abort_unless($own && in_array($own, $campuses, true), 403, 'Your account has no campus.');

        return $own;
    }

    // ['schlyear' => ..., 'semester' => int] of the request; only configured terms
    protected function schedulingTerm(Request $request): array
    {
        $term = $request->validate([
            'schlyear' => ['required', 'string', Rule::in($this->schoolYears()->all())],
            'semester' => ['required', 'integer', Rule::in(array_keys(ConfigureCurrent::SEMESTERS))],
        ], [], ['schlyear' => 'school year']);

        return ['schlyear' => $term['schlyear'], 'semester' => (int) $term['semester']];
    }

    // Campus, school year and semester selects of a page (the active term selected)
    protected function schedulingOptions(): array
    {
        $active = ConfigureCurrent::active();

        return [
            'campuses' => $this->searchableCampuses(),
            'userCampus' => $this->campuses()[0] ?? null,
            'schoolYears' => $this->schoolYears(),
            'semesters' => ConfigureCurrent::SEMESTERS,
            'activeYear' => $active?->schlyear,
            'activeSemester' => $active ? (int) $active->semester : null,
        ];
    }

    protected function schoolYears()
    {
        return ConfigureCurrent::orderByDesc('id')->pluck('schlyear')->unique()->values();
    }

    // 404 unless the record's campus (a code or a list such as "MC, VC") is one the user may manage
    protected function authorizeCampus(?string $campus): void
    {
        $allowed = array_keys($this->searchableCampuses());

        abort_unless(array_intersect(CampusList::split($campus), $allowed), 404, 'Record not found in your campus.');
    }

    // Programs offered in a campus (programs.campus is a list); Graduate School Staff: graduate programs only
    protected function programsOf(string $campus)
    {
        return EnPrograms::whereCampusIn('campus', [$campus])
            ->when($this->user()->isGraduateSchoolStaff(), fn ($query) => $query->where('progCod', 'like', '%' . config('ciss.scheduling.graduate_program_code') . '%'))
            ->orderBy('progAcronym')
            ->get(['id', 'progCod', 'progAcronym', 'progName']);
    }

    // Runs $callback while holding a lock on $key, so a duplicate check and its insert / update cannot interleave
    protected function exclusively(string $key, Closure $callback)
    {
        try {
            return Cache::lock('scheduling:' . $key, 10)->block(5, $callback);
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException) {
            throw ValidationException::withMessages(['id' => 'Someone else is saving the same record. Please try again.']);
        }
    }
}
