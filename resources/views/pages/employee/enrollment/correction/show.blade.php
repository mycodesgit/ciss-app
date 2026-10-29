@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Correction of Grades
@endsection

@section('workspace')
    {{-- URL and grade codes for enrollment/grade-correction.js (rows loaded by AJAX, re-drawn after every correction) --}}
    <div id="gradeCorrection"
        data-students-url="{{ route('employee.enrollment.corrections.students', $subject->id) }}"
        data-grade-codes='@json($gradeCodes)'>

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
                                <li class="breadcrumb-item mt-1">
                                    <a href="{{ route('employee.enrollment.corrections.index') }}" wire:navigate>Correction of Grades</a>
                                </li>
                                <li class="breadcrumb-item active mt-1"><strong>{{ $subject->sub_name }} {{ $subject->subSec }}</strong></li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Correction of Grades</h1>
                            <p class="text-muted small mb-0">{{ $subject->sub_name }} &mdash; {{ $subject->sub_title }} &middot; {{ $subject->subUnit }} unit(s)</p>
                        </div>
                        <div class="btn btn-light">
                            <i class="ti ti-circle-filled text-success"></i> Selected:
                            <i class="ti ti-calendar"></i> {{ $term->schlyear }} - {{ $term->semester_name }}
                        </div>
                    </div>

                    <div class="alert alert-warning small">
                        <i class="ti ti-alert-triangle"></i>
                        Every change here is an official correction: it is saved right away (after you confirm), even for submitted grades,
                        and logged with your name. Submitted grades stay submitted.
                    </div>

                    <div class="card card-animate">
                        <div class="card-header pt-3">
                            <h6 class="card-title mb-0">
                                <i class="ti ti-server"></i> List of students | <strong>{{ $subject->sub_name }} {{ $subject->subSec }}</strong>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="correctionTable" class="table table-bordered text-nowrap align-middle mb-0" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th style="min-width: 120px;">Final Grade</th>
                                            <th style="min-width: 120px;">Completion</th>
                                            <th>Equivalent</th>
                                            <th>Unit</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="8" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-2"></span> Loading students...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/enrollment/grade-correction.js" />
@endpush
