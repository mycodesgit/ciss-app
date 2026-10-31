@extends('layouts.master_empscheduling')

@section('title')
    CISS - Scheduling | Class Rooms
@endsection

@section('workspace')
    {{-- URLs for scheduling/rooms.js (__ID__ is replaced with the room's id) --}}
    <div id="schedRooms"
        data-list-url="{{ route('employee.scheduling.rooms.list') }}"
        data-store-url="{{ route('employee.scheduling.rooms.store') }}"
        data-update-url="{{ route('employee.scheduling.rooms.update', '__ID__') }}"
        data-destroy-url="{{ route('employee.scheduling.rooms.destroy', '__ID__') }}">

        <div class="row">
            <div class="col-12">
                <div class="mb-6">
                    @include('pages.employee.scheduling.partials.header', [
                        'page' => 'Class Rooms',
                        'title' => 'Class Rooms',
                        'description' => 'The rooms of a campus where classes are scheduled.',
                    ])

                    @include('pages.employee.scheduling.partials.search-form', ['formId' => 'schedRoomsSearchForm', 'withTerm' => false])

                    <div class="card card-animate">
                        <div class="card-header d-flex justify-content-between align-items-center py-3">
                            <h6 class="card-title mb-0"><i class="ti ti-server"></i> List of Rooms</h6>
                            <button type="button" class="btn btn-success btn-sm text-light" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                                <i class="fas fa-plus"></i> Add New
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="schedRoomsTable" class="table table-hover" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>College</th>
                                            <th>Room Name</th>
                                            <th>Capacity</th>
                                            <th>Status</th>
                                            <th width="5%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach (['add' => ['Add New Room', 'store-url', 'POST'], 'edit' => ['Edit Room', 'update-url', 'PUT']] as $mode => [$title, $url, $method])
            <x-employee.form-modal :id="$mode . 'RoomModal'" :title="$title" :form-id="$mode . 'RoomForm'" :url="$url" :method="$method">
                {{-- Add: the searched campus (rooms.js); the server checks it --}}
                <input type="hidden" name="campus">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">College: <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" name="college_room" required>
                            <option value="" selected disabled>--Select--</option>
                            @foreach ($colleges as $id => $abbr)
                                <option value="{{ $id }}">{{ $abbr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Room Name: <span class="text-danger">*</span></label>
                        <input type="text" name="room_name" class="form-control form-control-sm" maxlength="100" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Capacity: <span class="text-danger">*</span></label>
                        <input type="number" name="room_capacity" class="form-control form-control-sm" min="1" max="1000" required>
                    </div>
                    @if ($mode === 'edit')
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Status: <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm" name="status" required>
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </x-employee.form-modal>
        @endforeach
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/scheduling/common.js" />
    <x-script src="js/employee/scheduling/rooms.js" />
@endpush
