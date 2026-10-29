@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Edit Duplicate Fees
@endsection

@section('workspace')
    {{-- URLs for enrollment/dup-fees.js (__ID__ is replaced with the fee line's id) --}}
    <div id="duplicateFees"
        data-list-url="{{ $listUrl }}"
        data-delete-url="{{ $deleteUrl }}"
        data-rf-url="{{ $rfUrl }}"
        data-student-name="{{ $student->lname }}, {{ $student->fname }}">

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
                                    <a href="{{ route('employee.enrollment.dup-fees.index') }}" wire:navigate>Edit Duplicate Fees</a>
                                </li>
                                <li class="breadcrumb-item active mt-1">{{ $student->stud_id }}</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Edit Duplicate Fees</h1>
                            <p class="text-muted small mb-0">Delete fee lines that were saved more than once.</p>
                        </div>
                        <div class="btn btn-light">
                            <i class="ti ti-circle-filled text-success"></i> Selected:
                            <i class="ti ti-calendar"></i> {{ $term->schlyear }} - {{ $term->semester_name }}
                            &middot; {{ str_replace(' Campus', '', config('ciss.campuses')[$enrollment->campus] ?? $enrollment->campus) }}
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-10">
                            {{-- Student Information --}}
                            <div class="card card-animate mb-3">
                                <div class="card-header pt-3">
                                    <h6 class="card-title">
                                        <i class="ti ti-user"></i> Student Information Section
                                    </h6>
                                </div>
                                <div class="card-body card-body-bg-color">
                                    <div class="row g-3">
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold">Student ID:</label>
                                            <input type="text" class="form-control form-control-sm" value="{{ $student->stud_id }}" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Last Name:</label>
                                            <input type="text" class="form-control form-control-sm" value="{{ $student->lname }}" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">First Name:</label>
                                            <input type="text" class="form-control form-control-sm" value="{{ $student->fname }}" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Middle Name:</label>
                                            <input type="text" class="form-control form-control-sm" value="{{ $student->mname }}" readonly>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label fw-semibold">Ext. Name:</label>
                                            <input type="text" class="form-control form-control-sm" value="{{ $student->ext }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Appraisal fee lines (dup-fees.js); only lines marked Duplicate can be deleted --}}
                            <div class="card card-animate mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center pt-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-receipt"></i> Student Appraisal Fees Section
                                    </h6>
                                    <small class="text-muted js-duplicate-summary"></small>
                                </div>
                                <div class="card-body card-body-bg-color table-responsive">
                                    <table id="duplicateFeesTable" class="table table-striped table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Fund</th>
                                                <th>Account</th>
                                                <th class="text-end">Amount</th>
                                                <th class="text-center">Copies</th>
                                                <th width="5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td colspan="5" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-2"></span> Loading fees...</td></tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold">
                                                <td colspan="2" class="text-end">Total</td>
                                                <td class="text-end js-fee-total">0.00</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Actions + totals (sticky) --}}
                        <div class="col-md-2">
                            <div class="position-sticky" style="top: 80px;">
                                <div class="card card-animate mb-3">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="ti ti-settings"></i> Action Buttons
                                        </h6>
                                    </div>
                                    <div class="card-body card-body-bg-color d-grid gap-2">
                                        <a href="{{ route('employee.enrollment.dup-fees.index') }}" wire:navigate class="btn btn-secondary btn-sm">Edit New</a>
                                        <button type="button" class="btn btn-success btn-sm" id="printRfButton">Print RF</button>
                                    </div>
                                </div>

                                <div class="card card-animate mb-3">
                                    <div class="card-body card-body-bg-color">
                                        <div class="row g-2 small">
                                            <div class="col-6">Tuition: <input type="text" class="form-control form-control-sm" value="{{ number_format($totalLecFee, 2) }}" readonly></div>
                                            <div class="col-6">Lab Fee: <input type="text" class="form-control form-control-sm" value="{{ number_format($totalLabFee, 2) }}" readonly></div>
                                        </div>
                                    </div>
                                </div>

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
    <x-script src="js/employee/enrollment/dup-fees.js" />
@endpush
