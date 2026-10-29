@extends('layouts.master_empadmission')

@section('title')
    CISS - Admission | Examination Results Report
@endsection

@section('workspace')
    {{-- URLs for report-results.js --}}
    <div id="resultsReport"
        data-list-url="{{ route('employee.admission.reports.results.list') }}"
        data-pdf-url="{{ route('employee.admission.reports.results.pdf') }}">

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
                                <li class="breadcrumb-item active mt-1">Examination Results</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Examination Results</h1>
                            <p class="text-muted small mb-0">Applicants of the selected year, campus and strand with their exam remarks (applicants with an exam result that are not yet confirmed). Export to Excel or print the list as PDF.</p>
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
                                        <i class="ti ti-file-text"></i> List of Examination Results
                                    </h6>
                                    <small class="text-muted">Applicants with an encoded exam result.</small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive p-2">
                                        <table id="appsresultlistTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>App ID</th>
                                                    <th>Name</th>
                                                    <th>Type</th>
                                                    <th>Contact</th>
                                                    <th>Remarks</th>
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

        @include('pages.employee.admission.shared.pdf-modal', ['id' => 'resultsPdfModal', 'title' => 'Examination Results'])
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/admission/common.js" />
    <x-script src="js/employee/admission/report-results.js" />
@endpush
