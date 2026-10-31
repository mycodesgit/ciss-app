@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Students Enrolled
@endsection

@section('workspace')
    {{-- URL for enrollment/students-enrolled.js (the table, via CissCrud) --}}
    <div id="studentsEnrolled" data-list-url="{{ route('employee.enrollment.students-enrolled.list') }}">

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
                                <li class="breadcrumb-item active mt-1">Students Enrolled</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Students Enrolled</h1>
                            <p class="text-muted small mb-0">Every student enrolled in a term with their strand, birthdate, age, sex, program and address.</p>
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
                                    <form id="studentsEnrolledSearchForm" autocomplete="off">
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

                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">School Year: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="schlyear" required>
                                                    @foreach ($schoolYears as $schoolYear)
                                                        <option value="{{ $schoolYear }}">{{ $schoolYear }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Semester: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="semester" required>
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach ($semesters as $semester)
                                                        <option value="{{ $semester }}">{{ \App\Models\SettingDB\ConfigureCurrent::SEMESTERS[$semester] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Type: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="type" required>
                                                    @foreach ($types as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
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
                                        <i class="ti ti-registered"></i> List of Students Enrolled
                                    </h6>
                                    <small class="text-muted js-search-shown"></small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="studentsEnrolledTable" class="table table-hover text-nowrap" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Student ID</th>
                                                    <th>Name</th>
                                                    <th>Strand</th>
                                                    <th>Birthdate</th>
                                                    <th>Age</th>
                                                    <th>Sex</th>
                                                    <th>Type</th>
                                                    <th>Program</th>
                                                    <th>Acronym</th>
                                                    <th>Year</th>
                                                    <th>Section</th>
                                                    <th>Term</th>
                                                    <th>Address</th>
                                                    <th>Barangay</th>
                                                    <th>City</th>
                                                    <th>Province</th>
                                                    <th>Region</th>
                                                    <th>Zip</th>
                                                    <th>Last School Attended</th>
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
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/enrollment/students-enrolled.js" />
@endpush
