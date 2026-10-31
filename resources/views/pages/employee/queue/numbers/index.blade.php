@extends('layouts.master_empqueue')

@section('title')
    CISS - Queueing | Numbers
@endsection

@section('workspace')
    {{-- URLs for queue/numbers.js (the list via CissCrud; the summary; Generate) --}}
    <div id="queueNumbers"
        data-list-url="{{ route('employee.queue.numbers.list', ['campus' => $campus]) }}"
        data-summary-url="{{ route('employee.queue.numbers.summary', ['campus' => $campus]) }}"
        data-store-url="{{ route('employee.queue.numbers.store', ['campus' => $campus]) }}">

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
                                <li class="breadcrumb-item active mt-1">Numbers</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Numbers</h1>
                            <p class="text-muted small mb-0">The queue numbers (tickets) of each transaction: generate them and see how far each one is.</p>
                        </div>
                        @include('pages.employee.queue.partials.campus-select')
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-lg-4">
                            <div class="card card-animate h-100">
                                <div class="card-header pt-3">
                                    <h6 class="card-title mb-0"><i class="ti ti-plus"></i> Generate Numbers</h6>
                                </div>
                                <div class="card-body">
                                    <form id="queueNumbersForm" autocomplete="off">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Category: <span class="text-danger">*</span></label>
                                                <select name="catname" class="form-control form-control-sm" required>
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category }}">{{ $category }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold">Start: <span class="text-danger">*</span></label>
                                                <input type="number" name="start" class="form-control form-control-sm" min="1" max="9999" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold">End: <span class="text-danger">*</span></label>
                                                <input type="number" name="end" class="form-control form-control-sm" min="1" max="9999" required>
                                            </div>
                                            <div class="col-12">
                                                <small class="text-muted d-block mb-2 js-preview">e.g. Enrollment 1 to 1000 makes ENRO-0001 to ENRO-1000. Numbers that already exist are skipped.</small>
                                                <button type="submit" class="btn btn-success btn-sm"><i class="ti ti-device-floppy"></i> Generate</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card card-animate h-100">
                                <div class="card-header pt-3">
                                    <h6 class="card-title mb-0"><i class="ti ti-chart-bar"></i> Numbers per Category</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Numbers</th>
                                                    <th>Range</th>
                                                    <th>Waiting</th>
                                                    <th>Serving</th>
                                                    <th>Served</th>
                                                </tr>
                                            </thead>
                                            <tbody class="js-summary-rows"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-animate">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                            <h6 class="card-title mb-0"><i class="ti ti-numbers"></i> List of Numbers</h6>
                            <select class="form-control form-control-sm js-list-category" style="width: auto;" aria-label="Category">
                                <option value="" selected disabled>Pick a category to list</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="queueNumbersTable" class="table table-hover" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Queue Number</th>
                                            <th>Status</th>
                                            <th>Window</th>
                                            <th>Last Change</th>
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
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/queue/numbers.js" />
@endpush
