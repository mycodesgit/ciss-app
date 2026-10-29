@extends('layouts.master_empadmission')

@section('title')
    CISS - Admission | Examination Results
@endsection

@section('workspace')
    {{-- URLs for results.js (__ID__ is replaced with the applicant's id). View Data reuses the applicants route. --}}
    <div id="results"
        data-list-url="{{ route('employee.admission.results.list') }}"
        data-update-url="{{ route('employee.admission.applicants.update', '__ID__') }}"
        data-result-url="{{ route('employee.admission.results.result', '__ID__') }}"
        data-push-url="{{ route('employee.admission.results.push', '__ID__') }}">

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
                                <li class="breadcrumb-item active mt-1">Examination Results</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Examination Results</h1>
                            <p class="text-muted small mb-0">Examinees with encoded test results: correct results, mark second qualifiers, and push passing examinees to the Confirm list.</p>
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
                                    @include('pages.employee.admission.shared.search-form')
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card card-animate">
                                <div class="card-header d-flex justify-content-between align-items-center py-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-server"></i> Examination Results
                                    </h6>
                                    <small class="text-muted">Only examinees of your campus can be managed.</small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive p-2">
                                        <table id="exresultlistTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>App ID</th>
                                                    <th>Name</th>
                                                    <th>Type</th>
                                                    <th>Raw Score</th>
                                                    <th>Remarks</th>
                                                    <th>Exam Sched</th>
                                                    <th>Campus</th>
                                                    <th>Strand</th>
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
        @include('pages.employee.admission.shared.result-modal', ['title' => 'Update Test Result', 'withQualifier' => true])
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/admission/common.js" />
    <x-script src="js/employee/admission/results.js" />
@endpush
