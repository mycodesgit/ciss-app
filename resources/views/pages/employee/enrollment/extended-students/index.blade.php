@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Extended Students
@endsection

@section('workspace')
    {{-- URL for enrollment/extended-students.js (the table, via CissCrud) --}}
    <div id="extendedStudents" data-list-url="{{ route('employee.enrollment.extended-students.list') }}">

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
                                <li class="breadcrumb-item active mt-1">Extended Students</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Extended Students</h1>
                            <p class="text-muted small mb-0">Students enrolled in more than 10 semesters.</p>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <div class="card card-animate">
                                <div class="card-header pt-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-server"></i> List of Extended Students
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="extendedStudTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Student ID Number</th>
                                                    <th>Name</th>
                                                    <th>No. of Sem</th>
                                                    <th>Campus</th>
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

        {{-- Enrollment history of one student (extended-students.js) --}}
        <div class="modal fade" id="extendedStudHisModal" tabindex="-1" aria-labelledby="extendedStudHisModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="extendedStudHisModalLabel">Enrollment History of <span class="js-student-name"></span></h5>
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
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/enrollment/extended-students.js" />
@endpush
