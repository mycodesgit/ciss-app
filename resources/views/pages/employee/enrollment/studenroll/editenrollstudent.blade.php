@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Edit Enrollment
@endsection

@section('workspace')
    {{-- URLs for enrollment/enroll-form.js (data-subjects: the saved subjects, shown when the page opens) --}}
    @php $studentTerm = ['student' => $student->stud_id, 'schlyear' => $term->schlyear, 'semester' => $term->semester]; @endphp
    <div id="enrollmentForm"
        data-section-url="{{ route('employee.enrollment.edit.section-subjects', $student->stud_id) }}"
        data-assess-url="{{ route('employee.enrollment.edit.assess', $student->stud_id) }}"
        data-save-url="{{ route('employee.enrollment.edit.update', $student->stud_id) }}"
        data-save-method="PUT"
        data-lock-after-save="0"
        data-rf-url="{{ route('employee.enrollment.enroll.registration-form', $studentTerm) }}"
        data-done-print-url="{{ route('employee.enrollment.edit.done-print', $student->stud_id) }}"
        data-delete-url="{{ route('employee.enrollment.edit.destroy', $student->stud_id) }}"
        data-student-name="{{ $student->lname }}, {{ $student->fname }}"
        data-subjects='@json($currentSubjects)'>

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
                                    <a href="{{ route('employee.enrollment.edit.index') }}" wire:navigate>Edit Enrollment</a>
                                </li>
                                <li class="breadcrumb-item active mt-1">{{ $student->stud_id }}</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Edit Enrollment</h1>
                            <p class="text-muted small mb-0">Complete the form below to edit student enrollment.</p>
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
                                        <a href="{{ route('employee.enrollment.edit.index') }}" wire:navigate class="btn btn-secondary btn-sm">Edit New</a>
                                        <button type="button" class="btn btn-success btn-sm js-lockable" id="assessButton">Assess</button>
                                        {{-- Enabled after a successful Assess --}}
                                        <button type="button" class="btn btn-success btn-sm" id="saveButton" disabled>Save</button>
                                        <button type="button" class="btn btn-outline-success btn-sm" id="printRfButton" @disabled(! $canPrint)>Print RF</button>
                                        @if ($isForPrinting)
                                            <button type="button" class="btn btn-info btn-sm text-white mt-3" id="donePrintButton">Done Print</button>
                                        @endif
                                        @if ($canDelete)
                                            <button type="button" class="btn btn-outline-danger btn-sm mt-3" id="deleteEnrollmentButton">
                                                <span style="font-size: 8pt;">Delete Enrollment</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @include('pages.employee.enrollment.studenroll.partials.form-totals')

                                <div class="card card-animate">
                                    <div class="card-body card-body-bg-color">
                                        <label class="form-label small fw-semibold">Posted By:</label>
                                        <input type="text" class="form-control form-control-sm" value="{{ $postedByName }}" readonly>
                                    </div>
                                </div>
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
