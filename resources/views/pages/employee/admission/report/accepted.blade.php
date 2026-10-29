@extends('layouts.master_empadmission')

@section('title')
    CISS - Admission | Accepted Applicants Report
@endsection

@section('workspace')
    {{-- URLs for report-accepted.js --}}
    <div id="acceptedReport"
        data-list-url="{{ route('employee.admission.reports.accepted.list') }}"
        data-pdf-url="{{ route('employee.admission.reports.accepted.pdf') }}">

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
                                <li class="breadcrumb-item active mt-1">Accepted Applicants</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Accepted Applicants</h1>
                            <p class="text-muted small mb-0">Applicants of the selected year, campus and strand accepted after the department interview, including those already pushed to enrollment. Export to Excel or print the list as PDF.</p>
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
                                        <i class="ti ti-file-code"></i> List of Accepted Applicants
                                    </h6>
                                    <small class="text-muted">Course: the course accepted in the interview.</small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive p-2">
                                        <table id="appsacceptedlistTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>App ID</th>
                                                    <th>Name</th>
                                                    <th>Type</th>
                                                    <th>Contact</th>
                                                    <th>Course</th>
                                                    <th>Status</th>
                                                    <th>Strand</th>
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

        @include('pages.employee.admission.shared.pdf-modal', ['id' => 'acceptedPdfModal', 'title' => 'Accepted Applicants'])
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/admission/common.js" />
    <x-script src="js/employee/admission/report-accepted.js" />
@endpush
