@extends('layouts.master_empadmission')

@section('title')
    CISS - Admission | Applicant
@endsection

@section('workspace')
    {{-- URLs for applicants.js (__ID__ is replaced with the applicant's id) --}}
    <div id="applicants"
        data-list-url="{{ route('employee.admission.applicants.list') }}"
        data-update-url="{{ route('employee.admission.applicants.update', '__ID__') }}"
        data-reupload-url="{{ route('employee.admission.applicants.reupload.update', '__ID__') }}"
        data-schedule-url="{{ route('employee.admission.applicants.schedule', '__ID__') }}"
        data-push-url="{{ route('employee.admission.applicants.push', '__ID__') }}"
        data-cancel-url="{{ route('employee.admission.applicants.cancel', '__ID__') }}">

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
                                <li class="breadcrumb-item active mt-1">List of Applicant</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">List of Applicant</h1>
                            <p class="text-muted small mb-0">View, manage, and process applicant records, requirements, and admission statuses across all programs.</p>
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
                                    {{-- Searching reloads the table below (no page reload) --}}
                                    <form id="applicantSearchForm">
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
                                                <label class="form-label fw-semibold">Strand:</label>
                                                <select class="form-control form-control-sm" name="strand">
                                                    <option value="">All strands</option>
                                                    @foreach ($strands as $strand)
                                                        <option value="{{ $strand->code }}">{{ $strand->strand }}</option>
                                                    @endforeach
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
                                        <i class="ti ti-server"></i> Registered Applicants
                                    </h6>
                                    <small class="text-muted">Only applicants of your campus can be managed.</small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive p-2">
                                        <table id="applistTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Admission ID</th>
                                                    <th>Name</th>
                                                    <th>Type</th>
                                                    <th>Contact</th>
                                                    <th>Date Registered</th>
                                                    <th>Campus</th>
                                                    <th>Strand</th>
                                                    <th>Schedule</th>
                                                    <th>Documents</th>
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

        @include('pages.employee.admission.applicant.partials.modals')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/employee/admission/applicants.js') }}"></script>
@endpush
