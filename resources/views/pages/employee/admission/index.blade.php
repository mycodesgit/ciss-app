@extends('layouts.master_empadmission')

@section('title')
    CISS - Admission | Dashboard
@endsection

@section('workspace')
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
                            <li class="breadcrumb-item active mt-1">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Admission Dashboard Overview</h1>
                        <p class="text-muted small mb-0">System metrics, admission application, and daily activity logs.</p>
                    </div>
                </div>

                <!-- Top KPI Cards -->
                <div class="row g-3 mb-3">
                    <div class="col-xl-3 col-sm-6">
                        <div class="card card-animate p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-medium">Total Registered</span>
                                <i class="ti ti-users text-muted fs-5"></i>
                            </div>
                            <div class="h2 fw-bold mb-1">{{ number_format($registeredCount) }}</div>
                            <small class="text-muted"><span class="text-success fw-semibold"><i class="ti ti-arrow-up-right"></i> Active</span> total applications</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-sm-6">
                        <div class="card card-animate p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-medium">Total Examinees</span>
                                <i class="ti ti-file-check text-muted fs-5"></i>
                            </div>
                            <div class="h2 fw-bold mb-1">{{ number_format($examineeCount) }}</div>
                            <small class="text-muted"><span class="text-success fw-semibold"><i class="ti ti-check"></i> Confirmed</span> examinee</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-sm-6">
                        <div class="card card-animate p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-medium">Total Examinees w/ Results</span>
                                <i class="ti ti-number text-muted fs-5"></i>
                            </div>
                            <div class="h2 fw-bold mb-1">{{ number_format($withResultCount) }}</div>
                            <small class="text-muted"><span class="text-success fw-semibold">Encoded</span> Scores</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-sm-6">
                        <div class="card card-animate p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-medium">Total Confirmed Examinees</span>
                                <i class="ti ti-users-group text-muted fs-5"></i>
                            </div>
                            <div class="h2 fw-bold mb-1">{{ number_format($confirmedCount) }}</div>
                            <small class="text-muted"><span class="text-success fw-semibold">Ready</span> for Department Interview</small>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-9">
                        <div class="card card-animate p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold mb-0">Number of Applicant</h6>
                                    <small class="text-muted">Admission in every campus in this year {{ $currentYear }}</small>
                                </div>
                                <div class="d-flex flex-row justify-content-end">
                                    <span class="mr-5">
                                        <i class="fas fa-square" style="color: #90ee90"></i> Registered Applicant: No schedule yet
                                    </span>
                                    &nbsp;&nbsp;
                                    <span class="mr-2">
                                        <i class="fas fa-square" style="color: #00a65a"></i> Registered Applicant: Scheduled
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                {{-- Filled by dashboard.js from the chart endpoint --}}
                                <canvas id="admission-chart" data-url="{{ route('employee.admission.dashboard.chart') }}" height="350px"></canvas>
                            </div>
                        </div>
                    </div>
                    <!-- Dynamic Calendar Widget -->
                    <div class="col-md-3">
                        <div class="card card-animate p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0"><i class="ti ti-calendar me-1"></i> Calendar</h6>
                                <small class="text-muted fw-semibold" id="calendarMonthYear"></small>
                            </div>
                            <div class="shadcn-calendar">
                                <div class="row g-1 text-center text-muted small fw-semibold mb-2">
                                    <div class="col">Su</div><div class="col">Mo</div><div class="col">Tu</div>
                                    <div class="col">We</div><div class="col">Th</div><div class="col">Fr</div><div class="col">Sa</div>
                                </div>
                                <div id="calendarDaysContainer"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/admission/dashboard.js" />
@endpush
