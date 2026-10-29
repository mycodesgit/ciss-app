@extends('layouts.master_empadmission')

@section('title')
    CISS - Admission | Examinee
@endsection

@section('workspace')
    {{-- URLs for examinees.js (__ID__ is replaced with the applicant's id). View/Schedule/Delete reuse the applicants routes. --}}
    <div id="examinees"
        data-list-url="{{ route('employee.admission.examinees.list') }}"
        data-update-url="{{ route('employee.admission.applicants.update', '__ID__') }}"
        data-schedule-url="{{ route('employee.admission.applicants.schedule', '__ID__') }}"
        data-cancel-url="{{ route('employee.admission.applicants.cancel', '__ID__') }}"
        data-result-url="{{ route('employee.admission.examinees.result', '__ID__') }}"
        data-push-url="{{ route('employee.admission.examinees.push', '__ID__') }}">

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
                                <li class="breadcrumb-item active mt-1">List of Examinee</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">List of Examinee</h1>
                            <p class="text-muted small mb-0">Applicants scheduled for the admission test: encode their exam results and push them to the result list.</p>
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
                                        <i class="ti ti-server"></i> Examinees
                                    </h6>
                                    <small class="text-muted">Only examinees of your campus can be managed.</small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive p-2">
                                        <table id="examlistTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>App ID</th>
                                                    <th>Name</th>
                                                    <th>Type</th>
                                                    <th>Contact No.</th>
                                                    <th>Exam Sched</th>
                                                    <th>Campus</th>
                                                    <th>Strand</th>
                                                    <th>RawScore</th>
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
        @include('pages.employee.admission.shared.schedule-modal')
        @include('pages.employee.admission.shared.document-modal')
        @include('pages.employee.admission.examinee.partials.result-modal')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/employee/admission/common.js') }}"></script>
    <script src="{{ asset('js/employee/admission/examinees.js') }}"></script>
@endpush
