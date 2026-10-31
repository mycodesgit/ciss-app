@extends('layouts.master_empqueue')

@section('title')
    CISS - Queueing | Counters
@endsection

@section('workspace')
    {{-- URLs for queue/counters.js (__ID__ is replaced with the counter's id) --}}
    <div id="queueCounters"
        data-list-url="{{ route('employee.queue.counters.list', ['campus' => $campus]) }}"
        data-store-url="{{ route('employee.queue.counters.store', ['campus' => $campus]) }}"
        data-update-url="{{ route('employee.queue.counters.update', '__ID__') }}"
        data-status-url="{{ route('employee.queue.counters.status', '__ID__') }}"
        data-destroy-url="{{ route('employee.queue.counters.destroy', '__ID__') }}">

        <div class="row">
            <div class="col-12">
                <div class="mb-6">
                    <div class="card mb-3" style=" background-color: #e9ecef; margin-top: -10px">
                        <div class="card-body">
                            <ol class="breadcrumb" style="margin-bottom: -3px;">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('employee.dashboard') }}" wire:navigate class="btn btn-success btn-sm text-light">
                                        <i class="fas fa-home"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item mt-1">Queueing</li>
                                <li class="breadcrumb-item active mt-1">Counters</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Counters</h1>
                            <p class="text-muted small mb-0">The windows of the campus: who mans each one and which transaction it serves.</p>
                        </div>
                        @include('pages.employee.queue.partials.campus-select')
                    </div>
                    <div class="card card-animate">
                        <div class="card-header d-flex justify-content-between align-items-center py-3">
                            <h6 class="card-title mb-0">
                                <i class="ti ti-window"></i> Counter in every window
                            </h6>
                            <button type="button" class="btn btn-success btn-sm text-light" data-bs-toggle="modal" data-bs-target="#addCounterModal">
                                <i class="ti ti-plus"></i> Add Counter
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="queueCountersTable" class="table table-hover" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Window</th>
                                            <th>Category</th>
                                            <th>Personnel</th>
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

        <x-employee.form-modal id="addCounterModal" title="Add Counter" form-id="addCounterForm" url="store-url">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Window No.: <span class="text-danger">*</span></label>
                    <input type="number" name="windowname" class="form-control" min="1" max="999" required>
                </div>
                @include('pages.employee.queue.counters.partials.fields')
            </div>
        </x-employee.form-modal>

        <x-employee.form-modal id="editCounterModal" title="Edit Counter" form-id="editCounterForm" url="update-url" method="PUT">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Window No.:</label>
                    <input type="text" class="form-control js-window" readonly tabindex="-1">
                </div>
                @include('pages.employee.queue.counters.partials.fields')
            </div>
        </x-employee.form-modal>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/queue/counters.js" />
@endpush
