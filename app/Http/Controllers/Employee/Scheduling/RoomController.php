<?php

namespace App\Http\Controllers\Employee\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Scheduling\Concerns\ScopesScheduling;
use App\Http\Controllers\Employee\Enrollment\Concerns\ChecksEnrollment;
use App\Http\Controllers\Employee\Enrollment\Concerns\FindsEnrollment;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\ScheduleDB\College;
use App\Models\ScheduleDB\Room;
use App\Models\ScheduleDB\SetClassSchedule;

/**
 * Scheduling > Class Rooms: the rooms of a campus (?campus=, see ScopesScheduling), each in a college.
 * A room name is unique in its campus. A room used by a class schedule cannot be deleted (set it Inactive).
 * (scheduling/rooms.js)
 */
class RoomController extends Controller
{
    use ChecksEnrollment, FindsEnrollment, ScopesScheduling;

    public function index()
    {
        return view('pages.employee.scheduling.rooms.index', $this->schedulingOptions() + [
            'colleges' => $this->colleges(),
            'statuses' => Room::STATUSES,
        ]);
    }

    public function list(Request $request)
    {
        $campus = $this->schedulingCampus($request);
        $colleges = $this->colleges();

        $rooms = Room::where('campus', $campus)->orderBy('room_name')
            ->get(['id', 'room_name', 'college_room', 'room_capacity', 'campus', 'status']);

        return response()->json([
            'data' => $rooms->map(fn (Room $room) => $room->only(['id', 'room_name', 'college_room', 'room_capacity', 'status']) + [
                'college' => $colleges[$room->college_room] ?? '',
                'status_label' => Room::STATUSES[$room->status] ?? $room->status,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $campus = $this->schedulingCampus($request);
        $data = $this->validated($request);

        return $this->exclusively("rooms.{$campus}", function () use ($campus, $data) {
            $this->ensureUniqueName($campus, $data['room_name']);
            Room::create($data + ['campus' => $campus, 'status' => Room::ACTIVE]);

            return response()->json(['success' => true, 'message' => "Room {$data['room_name']} added."]);
        });
    }

    public function update(Request $request, Room $room)
    {
        $this->authorizeCampus($room->campus);
        $data = $this->validated($request) + $request->validate(['status' => ['required', Rule::in(array_keys(Room::STATUSES))]]);

        return $this->exclusively("rooms.{$room->campus}", function () use ($room, $data) {
            $this->ensureUniqueName($room->campus, $data['room_name'], $room->id);
            $room->update($data);

            return response()->json(['success' => true, 'message' => "Room {$room->room_name} saved."]);
        });
    }

    public function destroy(Room $room)
    {
        $this->authorizeCampus($room->campus);

        if (SetClassSchedule::where('room_id', $room->id)->exists()) {
            throw ValidationException::withMessages(['id' => "Room {$room->room_name} is used by class schedules. Set it Inactive instead."]);
        }

        $room->delete();

        return response()->json(['success' => true, 'message' => "Room {$room->room_name} deleted."]);
    }

    // ---------- Helpers ----------

    private function validated(Request $request): array
    {
        $request->merge(['room_name' => trim((string) $request->input('room_name'))]);

        return $request->validate([
            'college_room' => ['required', Rule::in(array_keys($this->colleges()->all()))],
            'room_name' => ['required', 'string', 'max:100'],
            'room_capacity' => ['required', 'integer', 'min:1', 'max:1000'],
        ], [], ['college_room' => 'college', 'room_name' => 'room name', 'room_capacity' => 'capacity']);
    }

    private function ensureUniqueName(string $campus, string $name, ?int $exceptId = null): void
    {
        $taken = Room::where('campus', $campus)->where('room_name', $name)
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages(['room_name' => "Room {$name} already exists in this campus."]);
        }
    }

    // [college.id => college_abbr] of the academic colleges
    private function colleges()
    {
        return College::whereIn('id', config('ciss.scheduling.college_ids'))->orderBy('college_abbr')->pluck('college_abbr', 'id');
    }
}
