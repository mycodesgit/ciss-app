@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | View Student Grades
@endsection

@section('workspace')
    {{-- URL for enrollment/student-grades.js --}}
    <div id="studentGrades" data-find-url="{{ route('employee.enrollment.student-grades.find') }}">

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
                                <li class="breadcrumb-item active mt-1">View Student Grades</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">View Student Grades</h1>
                            <p class="text-muted small mb-0">All grades of a student, per school year and semester.</p>
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
                                    {{-- Searching loads the grades below (no page reload) --}}
                                    <form id="studentGradesSearchForm" autocomplete="off">
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
                                                <label class="form-label fw-semibold">Student ID Number: <span class="text-danger">*</span></label>
                                                {{-- Typed as 2024-0001-K (crud.js formats it) --}}
                                                <input type="text" name="stud_id" class="form-control form-control-sm js-student-id" placeholder="0000-0000-X" maxlength="11" required autofocus>
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

                        {{-- Filled by student-grades.js after a search --}}
                        <div class="col-md-12 d-none js-result">
                            <div class="card">
                                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-numbers"></i>
                                        <span class="js-student-id-shown"></span> &middot; <span class="js-student-name"></span>
                                    </h6>
                                    <small class="text-muted">
                                        <span class="js-student-campus"></span> &middot; Total credits earned: <strong class="js-total-credits"></strong>
                                    </small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover text-nowrap mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Course</th>
                                                    <th>Subject</th>
                                                    <th>Descriptive Title</th>
                                                    <th>Final Grade</th>
                                                    <th>Completion</th>
                                                    <th>Credit</th>
                                                </tr>
                                            </thead>
                                            <tbody class="js-grade-rows"></tbody>
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
    <x-script src="js/employee/enrollment/student-grades.js" />
@endpush
