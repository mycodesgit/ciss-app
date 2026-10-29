@extends('layouts.master_empadmission')

@section('title')
    CISS - Admission | Configure Admission
@endsection

{{-- URLs of one tab for config.js (__ID__ is replaced with the record's id) --}}
@php
    $urls = fn (string $section) => sprintf(
        'data-section="%s" data-list-url="%s" data-store-url="%s" data-record-url="%s"',
        $section,
        route("employee.admission.config.{$section}.list"),
        route("employee.admission.config.{$section}.store"),
        route("employee.admission.config.{$section}.update", '__ID__'),
    );
@endphp

@section('workspace')
    <div id="admissionConfig" data-date-options-url="{{ route('employee.admission.config.dates.options') }}">

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
                                <li class="breadcrumb-item mt-1">Admission</li>
                                <li class="breadcrumb-item active mt-1">Configure Admission</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Configure Admission</h1>
                            <p class="text-muted small mb-0">View, manage admission statuses across all programs.</p>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <ul class="nav nav-pills bg-light p-2 rounded-2 d-inline-flex flex-wrap gap-1 mb-3" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="pills-one-tab" data-bs-toggle="pill" data-bs-target="#pills-one" type="button" role="tab" aria-controls="pills-one" aria-selected="true">
                                        Programs
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-two-tab" data-bs-toggle="pill" data-bs-target="#pills-two" type="button" role="tab" aria-controls="pills-two" aria-selected="false" tabindex="-1">
                                        Strand
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-three-tab" data-bs-toggle="pill" data-bs-target="#pills-three" type="button" role="tab" aria-controls="pills-three" aria-selected="false" tabindex="-1">
                                        Admission Date
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-four-tab" data-bs-toggle="pill" data-bs-target="#pills-four" type="button" role="tab" aria-controls="pills-four" aria-selected="false" tabindex="-1">
                                        Time Schedule
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-five-tab" data-bs-toggle="pill" data-bs-target="#pills-five" type="button" role="tab" aria-controls="pills-five" aria-selected="false" tabindex="-1">
                                        Venue
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-six-tab" data-bs-toggle="pill" data-bs-target="#pills-six" type="button" role="tab" aria-controls="pills-six" aria-selected="false" tabindex="-1">
                                        Year
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content mt-3" id="pills-tabContent">
                                {{-- Programs --}}
                                <div class="tab-pane fade show active" id="pills-one" role="tabpanel" aria-labelledby="pills-one-tab" tabindex="0" {!! $urls('programs') !!}>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="card card-animate">
                                                <div class="card-header pt-3">
                                                    <h6 class="card-title">
                                                        <i class="ti ti-plus"></i> Add Programs
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <form data-store>
                                                        <div class="row g-3">
                                                            <div class="col-md-12">
                                                                <label class="form-label fw-semibold">Program Code: <span class="text-danger">*</span></label>
                                                                <input type="text" name="code" class="form-control form-control-sm" oninput="this.value = this.value.toUpperCase()" required>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <label class="form-label fw-semibold">Program Name: <span class="text-danger">*</span></label>
                                                                <input type="text" name="program" class="form-control form-control-sm" oninput="this.value = this.value.toUpperCase()" required>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <button type="submit" class="btn btn-success btn-sm w-100">Save</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="card card-animate">
                                                <div class="card-header pt-3">
                                                    <h6 class="card-title">
                                                        <i class="ti ti-list"></i> List of Programs
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive mt-1 p-2">
                                                        <table id="confprog" class="table table-hover" style="width: 100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Code</th>
                                                                    <th>Program</th>
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

                                {{-- Strand --}}
                                <div class="tab-pane fade" id="pills-two" role="tabpanel" aria-labelledby="pills-two-tab" tabindex="0" {!! $urls('strands') !!}>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="card card-animate">
                                                <div class="card-header pt-3">
                                                    <h6 class="card-title">
                                                        <i class="ti ti-plus"></i> Add Strand
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <form data-store>
                                                        <div class="row g-3">
                                                            <div class="col-md-12">
                                                                <label class="form-label fw-semibold">Strand Code: <span class="text-danger">*</span></label>
                                                                <input type="text" name="code" class="form-control form-control-sm" oninput="this.value = this.value.toUpperCase()" required>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <label class="form-label fw-semibold">Strand Name: <span class="text-danger">*</span></label>
                                                                <input type="text" name="strand" class="form-control form-control-sm" oninput="this.value = this.value.toUpperCase()" required>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <button type="submit" class="btn btn-success btn-sm w-100">Save</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="card card-animate">
                                                <div class="card-header pt-3">
                                                    <h6 class="card-title">
                                                        <i class="ti ti-list"></i> List of Strand
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive mt-1 p-2">
                                                        <table id="confstrand" class="table table-hover" style="width: 100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Code</th>
                                                                    <th>Strand</th>
                                                                    <th>Campus</th>
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

                                {{-- Admission Date --}}
                                <div class="tab-pane fade" id="pills-three" role="tabpanel" aria-labelledby="pills-three-tab" tabindex="0" {!! $urls('dates') !!}>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="card card-animate">
                                                <div class="card-header pt-3">
                                                    <h6 class="card-title">
                                                        <i class="ti ti-plus"></i> Add Admission Date
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <form data-store>
                                                        <div class="row g-3">
                                                            <div class="col-md-12">
                                                                <label class="form-label fw-semibold">Admission Date: <span class="text-danger">*</span></label>
                                                                <input type="date" name="date" class="form-control form-control-sm" required>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <button type="submit" class="btn btn-success btn-sm w-100">Save</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="card card-animate">
                                                <div class="card-header pt-3">
                                                    <h6 class="card-title">
                                                        <i class="ti ti-list"></i> List of Admission Date
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive mt-1 p-2">
                                                        <table id="adDate" class="table table-hover" style="width: 100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Campus</th>
                                                                    <th>Date</th>
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

                                {{-- Time Schedule --}}
                                <div class="tab-pane fade" id="pills-four" role="tabpanel" aria-labelledby="pills-four-tab" tabindex="0" {!! $urls('times') !!}>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="card card-animate">
                                                <div class="card-header pt-3">
                                                    <h6 class="card-title">
                                                        <i class="ti ti-plus"></i> Add Time Schedule
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <form data-store>
                                                        <div class="row g-3">
                                                            <div class="col-md-12">
                                                                <label class="form-label fw-semibold">Date: <span class="text-danger">*</span></label>
                                                                {{-- Options: this campus's admission dates (config.js) --}}
                                                                <select class="form-select form-select-sm js-date-options" name="date" required></select>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <label class="form-label fw-semibold">Time: <span class="text-danger">*</span></label>
                                                                <input type="time" name="time" class="form-control form-control-sm" required>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <label class="form-label fw-semibold">Slots: <span class="text-danger">*</span></label>
                                                                <input type="number" name="slots" min="1" class="form-control form-control-sm" required>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <button type="submit" class="btn btn-success btn-sm w-100">Save</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="card card-animate">
                                                <div class="card-header pt-3">
                                                    <h6 class="card-title">
                                                        <i class="ti ti-list"></i> List of Time Schedule
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive mt-1 p-2">
                                                        <table id="adTime" class="table table-hover" style="width: 100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Campus</th>
                                                                    <th>Date</th>
                                                                    <th>Time</th>
                                                                    <th>Slots</th>
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

                                {{-- Venue --}}
                                <div class="tab-pane fade" id="pills-five" role="tabpanel" aria-labelledby="pills-five-tab" tabindex="0" {!! $urls('venues') !!}>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="card card-animate">
                                                <div class="card-header pt-3">
                                                    <h6 class="card-title">
                                                        <i class="ti ti-plus"></i> Add Venue
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <form data-store>
                                                        <div class="row g-3">
                                                            <div class="col-md-12">
                                                                <label class="form-label fw-semibold">Admission Year: <span class="text-danger">*</span></label>
                                                                {{-- Options are rebuilt when a year is added (config.js) --}}
                                                                <select class="form-select form-select-sm js-year-options" name="adyear" required>
                                                                    @foreach ($years as $year)
                                                                        <option value="{{ $year }}" @selected($year == $currentYear)>{{ $year }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <label class="form-label fw-semibold">Admission Venue: <span class="text-danger">*</span></label>
                                                                <input type="text" name="venue" class="form-control form-control-sm js-capitalize" required>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <button type="submit" class="btn btn-success btn-sm w-100">Save</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="card card-animate">
                                                <div class="card-header pt-3">
                                                    <h6 class="card-title">
                                                        <i class="ti ti-list"></i> List of Venue
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive mt-1 p-2">
                                                        <table id="adVenue" class="table table-hover" style="width: 100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Campus</th>
                                                                    <th>Year</th>
                                                                    <th>Venue</th>
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

                                {{-- Year (shared by every campus: only users who can see all campuses can add / change) --}}
                                <div class="tab-pane fade" id="pills-six" role="tabpanel" aria-labelledby="pills-six-tab" tabindex="0" {!! $urls('years') !!}>
                                    <div class="row g-3">
                                        @if ($canManageYears)
                                            <div class="col-md-3">
                                                <div class="card card-animate">
                                                    <div class="card-header pt-3">
                                                        <h6 class="card-title">
                                                            <i class="ti ti-plus"></i> Add Year
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <form data-store>
                                                            <div class="row g-3">
                                                                <div class="col-md-12">
                                                                    <label class="form-label fw-semibold">Admission Year: <span class="text-danger">*</span></label>
                                                                    <input type="number" name="adyear" min="2000" max="2100" class="form-control form-control-sm" required>
                                                                    <small class="text-muted">New years start Off. Use Edit to switch a year On.</small>
                                                                </div>

                                                                <div class="col-md-12">
                                                                    <button type="submit" class="btn btn-success btn-sm w-100">Save</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div @class(['col-md-9' => $canManageYears, 'col-md-12' => ! $canManageYears])>
                                            <div class="card card-animate">
                                                <div class="card-header pt-3">
                                                    <h6 class="card-title">
                                                        <i class="ti ti-list"></i> List of Year
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive mt-1 p-2">
                                                        <table id="adYear" class="table table-hover" style="width: 100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Year</th>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('pages.employee.admission.configure.partials.config-modals')
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/admission/config.js" />
@endpush
