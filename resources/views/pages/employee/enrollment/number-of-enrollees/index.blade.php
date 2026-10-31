@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Number of Enrollees
@endsection

@section('workspace')
    {{-- URL for enrollment/number-of-enrollees.js --}}
    <div id="numberOfEnrollees" data-count-url="{{ route('employee.enrollment.number-of-enrollees.count') }}">

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
                                <li class="breadcrumb-item mt-1">Enrollment</li>
                                <li class="breadcrumb-item active mt-1">Number of Enrollees</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Number of Enrollees</h1>
                            <p class="text-muted small mb-0">Enrolled students of a campus and term per year level (graduate school not included).</p>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <div class="card card-animate">
                                <div class="card-header pt-3">
                                    <h6 class="card-title">
                                        <i class="ti ti-search"></i> Search to show data
                                    </h6>
                                </div>
                                <div class="card-body">
                                    {{-- Searching loads the counts below (no page reload) --}}
                                    <form id="numberOfEnrolleesSearchForm" autocomplete="off">
                                        <div class="row g-3">
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Campus: <span class="text-danger">*</span></label>
                                                {{-- Administrators: every campus; everyone else: their own --}}
                                                <select class="form-control form-control-sm" name="campus" required>
                                                    @foreach ($campuses as $code => $name)
                                                        <option value="{{ $code }}" @selected($code === $userCampus)>{{ str_replace(' Campus', '', $name) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">School Year: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="schlyear" required>
                                                    @foreach ($schoolYears as $schoolYear)
                                                        <option value="{{ $schoolYear }}">{{ $schoolYear }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Semester: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="semester" required>
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach ($semesters as $semester)
                                                        <option value="{{ $semester }}">{{ \App\Models\SettingDB\ConfigureCurrent::SEMESTERS[$semester] }}</option>
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

                        {{-- Filled by number-of-enrollees.js after a search --}}
                        <div class="col-md-12 d-none js-result">
                            <div class="card">
                                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-checklist"></i> Total Enrolled: <strong class="js-total"></strong>
                                    </h6>
                                    <small class="text-muted">
                                        <span class="js-campus"></span> &middot; <span class="js-term"></span>
                                    </small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover text-nowrap mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Year Level</th>
                                                    <th>Male</th>
                                                    <th>Female</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="js-year-rows"></tbody>
                                            <tfoot>
                                                <tr class="fw-bold">
                                                    <td class="text-end">Total:</td>
                                                    <td class="js-male"></td>
                                                    <td class="js-female"></td>
                                                    <td class="js-total"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/enrollment/number-of-enrollees.js" />
@endpush
