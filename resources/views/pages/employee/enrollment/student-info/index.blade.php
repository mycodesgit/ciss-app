@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Student Information
@endsection

@section('workspace')
    {{-- URLs for enrollment/student-information.js (table + edit form via CissCrud; __ID__ = the Student ID) --}}
    <div id="studentInfo"
        data-list-url="{{ route('employee.enrollment.student-info.list') }}"
        data-show-url="{{ route('employee.enrollment.student-info.show', '__ID__') }}"
        data-update-url="{{ route('employee.enrollment.student-info.update', '__ID__') }}">

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
                                <li class="breadcrumb-item active mt-1">Student Information</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Student Information</h1>
                            <p class="text-muted small mb-0">View and update the information of the students of your campus.</p>
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
                                    <form id="studentInfoSearchForm" autocomplete="off">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Enter Student Last name, First name or Student ID No.: <span class="text-danger">*</span></label>
                                                <input type="text" name="query" class="form-control form-control-sm text-uppercase" placeholder="Search Student Lastname, Firstname or Student ID No." minlength="2" maxlength="100" required autofocus>
                                            </div>
                                            {{-- Graduate school students (Student ID with -G) are in the Main Campus only --}}
                                            @if ($hasGraduateSchool)
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">Students:</label>
                                                    <select name="level" class="form-select form-select-sm">
                                                        <option value="undergraduate">Undergraduate</option>
                                                        <option value="graduate">Graduate School</option>
                                                    </select>
                                                </div>
                                            @endif
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
                                        <i class="ti ti-server"></i> List of Students
                                    </h6>
                                    <small class="text-muted js-limit-note"></small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="studentInfoTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Student ID Number</th>
                                                    <th>Name</th>
                                                    <th>Gender</th>
                                                    <th>Campus</th>
                                                    <th>Civil Status</th>
                                                    <th>Course</th>
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

        @include('pages.employee.enrollment.student-info.partials.student-modal')
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/enrollment/student-information.js" />
@endpush
