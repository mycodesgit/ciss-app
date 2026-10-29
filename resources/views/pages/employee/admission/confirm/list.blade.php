@extends('layouts.master_empadmission')

@section('title')
    CISS - Admission | Confirmed Applicants
@endsection

@section('workspace')
    {{-- URLs for confirmed.js (__ID__ is replaced with the applicant's id). View Data reuses the applicants route. --}}
    <div id="confirmed"
        data-can-record="{{ $canRecordInterviews ? 1 : 0 }}"
        data-list-url="{{ route('employee.admission.confirmed.list') }}"
        data-update-url="{{ route('employee.admission.applicants.update', '__ID__') }}"
        data-pre-enrollment-url="{{ route('employee.admission.applicants.pre-enrollment', '__ID__') }}"
        data-interview-url="{{ route('employee.admission.confirmed.interview', '__ID__') }}"
        data-push-url="{{ route('employee.admission.confirmed.push', '__ID__') }}">

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
                                <li class="breadcrumb-item active mt-1">Confirmed Applicants</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Confirmed Applicants</h1>
                            <p class="text-muted small mb-0">Applicants confirmed for the department interview: record the interview result and push completed interviews to the Accepted list.</p>
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
                                        <i class="ti ti-server"></i> Confirmed Applicants
                                    </h6>
                                    <small class="text-muted">
                                        @if ($canRecordInterviews)
                                            Only applicants of your campus can be managed.
                                        @else
                                            You can view interview results; recording them is done by the departments.
                                        @endif
                                    </small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive p-2">
                                        <table id="confrmlistTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>App ID</th>
                                                    <th>Name</th>
                                                    <th>Type</th>
                                                    <th>Contact No.</th>
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
        @include('pages.employee.admission.shared.pre-enrollment-modal')
        @include('pages.employee.admission.confirm.partials.interview-modal')
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/admission/common.js" />
    <x-script src="js/employee/admission/confirmed.js" />
@endpush
