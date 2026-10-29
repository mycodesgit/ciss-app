@extends('layouts.master_empadmission')

@section('title')
    CISS - Admission | Applicants Report
@endsection

@section('workspace')
    {{-- URLs for report-applicants.js (__ID__ is replaced with the applicant's id). View Data reuses the applicants route. --}}
    <div id="applicantsReport"
        data-list-url="{{ route('employee.admission.reports.applicants.list') }}"
        data-update-url="{{ route('employee.admission.applicants.update', '__ID__') }}"
        data-slip-url="{{ route('employee.admission.reports.applicants.slip', '__ID__') }}"
        data-slips-url="{{ route('employee.admission.reports.applicants.slips') }}"
        data-slips-per-zip="{{ $slipsPerZip }}">

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
                                <li class="breadcrumb-item active mt-1">Applicants</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Applicants Report</h1>
                            <p class="text-muted small mb-0">All applicants of the selected year, campus and strand (cancelled applicants are not included). Export to Excel, PDF or print.</p>
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
                                        <i class="ti ti-file-star"></i> List of Applicants
                                    </h6>
                                    <button type="button" class="btn btn-outline-danger btn-sm js-slip-batches">
                                        <i class="fas fa-file-archive"></i> Download Admission Slips
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive p-2">
                                        <table id="appsreplistTable" class="table table-hover" style="width:100%;">
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
                                                    <th>Last School Attended</th>
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

        @include('pages.employee.admission.shared.info-modal')
        @include('pages.employee.admission.shared.pdf-modal', ['id' => 'admissionSlipModal', 'title' => 'Admission Slip'])

        {{-- Admission Slips as ZIP files, one button per batch of the searched applicants (report-applicants.js) --}}
        <div class="modal fade" id="slipBatchModal" tabindex="-1" aria-labelledby="slipBatchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="slipBatchModalLabel">
                            <i class="fas fa-file-archive text-danger me-1"></i> Download Admission Slips
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-1 js-slip-batch-summary"></p>
                        <p class="small mb-3">To prevent timeouts, the slips are downloaded in batches of {{ $slipsPerZip }} applicants per ZIP file. A batch can take a minute to prepare.</p>
                        <div class="d-flex flex-wrap gap-2 js-slip-batch-buttons"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/admission/common.js" />
    <x-script src="js/employee/admission/report-applicants.js" />
@endpush
