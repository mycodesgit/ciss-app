<?php

namespace App\Http\Controllers\Employee\Queue\Concerns;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The campus a Queue page works on (?campus=): administrators can pick any campus, everyone else only their own
 * (FindsEnrollment::searchableCampuses). Without one, the user's first campus. Needs ChecksEnrollment and FindsEnrollment.
 */
trait PicksQueueCampus
{
    protected function queueCampus(Request $request): string
    {
        $campuses = array_keys($this->searchableCampuses());

        if ($request->filled('campus')) {
            return $request->validate(['campus' => ['required', Rule::in($campuses)]])['campus'];
        }

        $own = $this->campuses()[0] ?? null;
        abort_unless($own && in_array($own, $campuses, true), 403, 'Your account has no campus.');

        return $own;
    }

    // For the campus select of a page: [code => name] and the selected one
    protected function campusOptions(string $selected): array
    {
        return ['campuses' => $this->searchableCampuses(), 'campus' => $selected];
    }
}
