@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Enroll Student
@endsection

@section('workspace')
    {{-- URLs for enrollment/enroll-form.js --}}
    <div id="enrollmentForm"
        data-section-url="{{ route('employee.enrollment.enroll.section-subjects', $student->stud_id) }}"
        data-assess-url="{{ route('employee.enrollment.enroll.assess', $student->stud_id) }}"
        data-save-url="{{ route('employee.enrollment.enroll.store', $student->stud_id) }}"
        data-save-method="POST"
        data-lock-after-save="1"
        data-rf-url="{{ route('employee.enrollment.enroll.registration-form', ['student' => $student->stud_id, 'schlyear' => $term->schlyear, 'semester' => $term->semester]) }}"
        data-student-name="{{ $student->lname }}, {{ $student->fname }}"
        data-subjects="[]">

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
                                    <a href="{{ route('employee.enrollment.enroll.index') }}" wire:navigate>Enroll Student</a>
                                </li>
                                <li class="breadcrumb-item active mt-1">{{ $student->stud_id }}</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Enroll Student</h1>
                            <p class="text-muted small mb-0">Complete the form below to enroll the student.</p>
                        </div>
                        <div class="btn btn-light">
                            <i class="ti ti-circle-filled text-success"></i> Selected:
                            <i class="ti ti-calendar"></i> {{ $term->schlyear }} - {{ $term->semester_name }}
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-10">
                            @include('pages.employee.enrollment.studenroll.partials.enrollment-form')
                        </div>

                        {{-- Actions + totals (sticky) --}}
                        <div class="col-md-2">
                            <div class="position-sticky" style="top: 80px;">
                                <div class="card card-animate mb-3">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="ti ti-settings"></i> Enrollment Action Buttons
                                        </h6>
                                    </div>
                                    <div class="card-body card-body-bg-color d-grid gap-2">
                                        <a href="{{ route('employee.enrollment.enroll.index') }}" wire:navigate class="btn btn-secondary btn-sm">Enroll New</a>
                                        <button type="button" class="btn btn-info btn-sm text-white js-lockable" id="addSubjectModalButton" data-bs-toggle="modal" data-bs-target="#addSubjectModal">Add Subject</button>
                                        <button type="button" class="btn btn-success btn-sm js-lockable" id="assessButton">Assess</button>
                                        {{-- Enabled after a successful Assess; any change of section, subjects, status, type or transferee needs a new Assess --}}
                                        <button type="button" class="btn btn-success btn-sm" id="saveButton" disabled>Save</button>
                                        <button type="button" class="btn btn-outline-success btn-sm" id="printRfButton" disabled title="Available after saving">Print RF</button>
                                    </div>
                                </div>

                                @include('pages.employee.enrollment.studenroll.partials.form-totals')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-employee.pdf-modal id="registrationFormModal" title="Registration Form" />
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/enrollment/enroll-form.js" />
@endpush
