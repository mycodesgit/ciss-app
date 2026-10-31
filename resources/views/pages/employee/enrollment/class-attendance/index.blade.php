@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Class Attendance
@endsection

@section('workspace')
    {{-- URL for enrollment/class-attendance.js (the table, via CissCrud) --}}
    <div id="classAttendance" data-list-url="{{ route('employee.enrollment.class-attendance.list') }}">

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
                                <li class="breadcrumb-item active mt-1">Class Attendance</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Class Attendance</h1>
                            <p class="text-muted small mb-0">Student class attendance sheet per subject offered in a term.</p>
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
                                    <form id="classAttendanceSearchForm" autocomplete="off">
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

                        <div class="col-md-12">
                            <div class="card card-animate">
                                <div class="card-header d-flex justify-content-between align-items-center pt-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-server"></i> Subjects Offered
                                    </h6>
                                    <small class="text-muted js-term-shown"></small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="classAttendanceTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th width="30%">Descriptive Title</th>
                                                    <th>Course Yr & Section</th>
                                                    <th>No. of Students</th>
                                                    <th>Male</th>
                                                    <th>Female</th>
                                                    <th width="5%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                            <tfoot>
                                                <tr class="fw-bold">
                                                    <td colspan="3" class="text-end">Total:</td>
                                                    <td class="js-total"></td>
                                                    <td class="js-male"></td>
                                                    <td class="js-female"></td>
                                                    <td></td>
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

        {{-- One subject: Student List and Attendance Sheet (PDF) tabs (class-attendance.js) --}}
        <div class="modal fade" id="classAttendanceModal" tabindex="-1" aria-labelledby="classAttendanceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="classAttendanceModalLabel">Class Attendance - <span class="js-class-name"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-pills bg-light p-2 rounded-2 mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active js-first-tab" data-bs-toggle="pill" data-bs-target="#classAttendanceListTab" type="button" role="tab">
                                    <i class="ti ti-list"></i> Student List
                                </button>
                            </li>
                            <li class="nav-item ms-1" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#classAttendancePdfTab" type="button" role="tab">
                                    <i class="fas fa-file-pdf"></i> Attendance Sheet
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="classAttendanceListTab" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th>Student ID No.</th>
                                                <th>Name</th>
                                                <th>Gender</th>
                                            </tr>
                                        </thead>
                                        <tbody class="js-student-rows"></tbody>
                                    </table>
                                </div>
                            </div>
                            {{-- The PDF loads when this tab is first opened --}}
                            <div class="tab-pane fade" id="classAttendancePdfTab" role="tabpanel">
                                <iframe class="js-class-pdf w-100 border rounded" style="height: 70vh;"></iframe>
                            </div>
                        </div>
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
    <x-script src="js/employee/enrollment/class-attendance.js" />
@endpush
