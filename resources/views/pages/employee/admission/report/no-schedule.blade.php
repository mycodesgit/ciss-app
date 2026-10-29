@extends('layouts.master_empadmission')

@section('title')
    CISS - Admission | Applicants No Schedule
@endsection

@section('workspace')
    {{-- URLs for report-no-schedule.js --}}
    <div id="noScheduleReport"
        data-list-url="{{ route('employee.admission.reports.no-schedule.list') }}"
        data-pdf-url="{{ route('employee.admission.reports.no-schedule.pdf') }}">

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
                                <li class="breadcrumb-item active mt-1">Applicants No Schedule</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Applicants No Schedule</h1>
                            <p class="text-muted small mb-0">Applicants of the selected year, campus and strand that have no exam schedule or no venue yet (cancelled applicants are not included). Export to Excel.</p>
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
                                    @include('pages.employee.admission.shared.search-form', ['requireStrand' => true])
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card card-animate">
                                <div class="card-header d-flex justify-content-between align-items-center py-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-file-alert"></i> List of Applicants with No Schedule / Venue
                                    </h6>
                                    <small class="text-muted">Set their schedule in List of Applicant or List of Examinee.</small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive p-2">
                                        <table id="appsnoschedlistTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>App ID</th>
                                                    <th>Name</th>
                                                    <th>Type</th>
                                                    <th>Strand</th>
                                                    <th>Email</th>
                                                    <th>Contact No.</th>
                                                    <th>Campus</th>
                                                    <th>Date Registered</th>
                                                    <th>Missing</th>
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

        @include('pages.employee.admission.shared.pdf-modal', ['id' => 'noSchedulePdfModal', 'title' => 'Applicants No Schedule'])
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/admission/common.js" />
    <x-script src="js/employee/admission/report-no-schedule.js" />
@endpush
