@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Enrollment History
@endsection

@section('workspace')
    {{-- URL for enrollment/enrollment-history.js (the table, via CissCrud) --}}
    <div id="enrollmentHistory" data-list-url="{{ route('employee.enrollment.history.search') }}">

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
                                <li class="breadcrumb-item active mt-1">Enrollment History</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Student Enrollment History</h1>
                            <p class="text-muted small mb-0">View student enrollment history.</p>
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
                                    {{-- Searching reloads the table below (no page reload) --}}
                                    <form id="historySearchForm" autocomplete="off">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Enter Student Last name or Student ID No.: <span class="text-danger">*</span></label>
                                                <input type="text" name="query" class="form-control form-control-sm text-uppercase" placeholder="Search Student Lastname or Student ID No." minlength="2" required autofocus>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="d-flex flex-column h-100">
                                                    <label class="form-label fw-semibold opacity-0 d-none d-md-block">Action</label>
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="ti ti-search"></i> OK
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
                                <div class="card-header d-flex justify-content-between align-items-center pt-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-server"></i> List of search show data
                                    </h6>
                                    <small class="text-muted js-limit-note"></small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="studhisTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Student ID Number</th>
                                                    <th>Last Name</th>
                                                    <th>First Name</th>
                                                    <th>Middle Name</th>
                                                    <th>Extension</th>
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

        {{-- Enrollment history of one student (enrollment-history.js) --}}
        <div class="modal fade" id="viewStudHisModal" tabindex="-1" aria-labelledby="viewStudHisModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewStudHisModalLabel">Enrollment History of <span class="js-student-name"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>StudentID</th>
                                        <th>School Year</th>
                                        <th>Semester</th>
                                        <th>Course</th>
                                        <th>Year Level</th>
                                        <th>Section</th>
                                        <th>Campus</th>
                                    </tr>
                                </thead>
                                <tbody class="js-history-rows"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <x-employee.pdf-modal id="preEnrollmentFormModal" title="Pre-Enrollment Form" />
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/enrollment/enrollment-history.js" />
@endpush
