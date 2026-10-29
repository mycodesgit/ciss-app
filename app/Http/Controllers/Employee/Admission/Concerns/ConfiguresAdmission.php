<?php

namespace App\Http\Controllers\Employee\Admission\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Shared by the Configure Admission tabs (Programs, Strand, Admission Date, Time Schedule, Venue, Year):
 * campus rules, row data and JSON replies.
 */
trait ConfiguresAdmission
{
    protected function user()
    {
        return Auth::guard('web')->user();
    }

    // Records belong to one campus; only users of that campus can change them
    protected function authorizeOwnCampus(Model $record): void
    {
        abort_unless($record->campus === $this->user()->campus, 403, 'You can only manage records of your own campus.');
    }

    // Users who can see every campus get all records, everyone else only their own campus
    protected function visibleToUser($query)
    {
        return $this->user()->canViewAllCampuses() ? $query : $query->where('campus', $this->user()->campus);
    }

    // Columns every campus-owned row sends to the tables
    protected function campusFields(Model $record): array
    {
        return [
            'campus_name' => str_replace(' Campus', '', config('ciss.campuses')[$record->campus] ?? $record->campus),
            'can_manage' => $record->campus === $this->user()->campus,
        ];
    }

    protected function ok(string $message)
    {
        return response()->json(['success' => true, 'message' => $message]);
    }

    protected function fail(string $message)
    {
        return response()->json(['success' => false, 'message' => $message], 422);
    }
}
