@extends('layouts.master_empadmission')

@section('title')
    CISS - Admission | Admission Schedules
@endsection

@section('workspace')
    {{-- URLs for report-schedules.js --}}
    <div id="schedulesReport"
        data-list-url="{{ route('employee.admission.reports.schedules.list') }}"
        data-schedules-url="{{ route('employee.admission.reports.schedules.options') }}"
        data-pdf-url="{{ route('employee.admission.reports.schedules.pdf') }}">

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
                                <li class="breadcrumb-item mt-1">Reports</li>
                                <li class="breadcrumb-item active mt-1">Admission Schedules</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Admission Schedules</h1>
                            <p class="text-muted small mb-0">Applicants booked on an examination schedule, with their venue. Export to Excel or print the list as PDF.</p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="card card-animate">
                                <div class="card-header pt-3">
                                    <h6 class="card-title">
                                        <i class="ti ti-search"></i> Search to show data
                                    </h6>
                                </div>
                                <div class="card-body">
                                    {{-- Changing Year / Campus reloads the schedules; Search reloads the table (no page reload) --}}
                                    <form id="scheduleSearchForm">
                                        <div class="row g-3">
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Year: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="year">
                                                    @foreach ($years as $year)
                                                        <option value="{{ $year }}" @selected($year == $currentYear)>{{ $year }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Campus: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="campus">
                                                    @foreach ($campuses as $code => $name)
                                                        <option value="{{ $code }}" @selected($code === $userCampus)>{{ str_replace(' Campus', '', $name) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Admission Date & Time: <span class="text-danger">*</span></label>
                                                {{-- Options: the year / campus exam schedules with booked/slots (report-schedules.js) --}}
                                                <select class="form-control form-control-sm" name="schedule" required>
                                                    <option value="">Loading schedules...</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="d-flex flex-column h-100">
                                                    <label class="form-label fw-semibold opacity-0 d-none d-md-block">Action</label>
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="ti ti-search"></i> Search
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card card-animate">
                                <div class="card-header d-flex justify-content-between align-items-center py-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-file-analytics"></i> List of Applicants per Schedule
                                    </h6>
                                    <small class="text-muted js-schedule-shown"></small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive p-2">
                                        <table id="appsschedlistTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>App ID</th>
                                                    <th>Name</th>
                                                    <th>Type</th>
                                                    <th>Contact</th>
                                                    <th>Date & Time</th>
                                                    <th>Venue</th>
                                                    <th>Campus</th>
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

        @include('pages.employee.admission.shared.pdf-modal', ['id' => 'schedulePdfModal', 'title' => 'Admission Schedule'])
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/admission/common.js" />
    <x-script src="js/employee/admission/report-schedules.js" />
@endpush
