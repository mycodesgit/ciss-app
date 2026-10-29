@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Enroll Student
@endsection

@section('workspace')
    {{-- URLs for enrollment/enroll-form.js --}}
    <div id="enrollmentForm"
        data-section-url="{{ route('employee.enrollment.enroll.section-subjects', $student->stud_id) }}"
        data-assess-url="{{ route('employee.enrollment.enroll.assess', $student->stud_id) }}"
        data-store-url="{{ route('employee.enrollment.enroll.store', $student->stud_id) }}"
        data-rf-url="{{ route('employee.enrollment.enroll.registration-form', ['student' => $student->stud_id, 'schlyear' => $term->schlyear, 'semester' => $term->semester]) }}"
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

                            {{-- Course: only choices are sent; program, year / section, units and fees are set by the server --}}
                            <div class="card card-animate mb-3">
                                <div class="card-header pt-3">
                                    <h6 class="card-title">
                                        <i class="ti ti-school"></i> Student Course Section
                                    </h6>
                                </div>
                                <div class="card-body card-body-bg-color">
                                    <form id="enrollForm" autocomplete="off">
                                        <input type="hidden" name="schlyear" value="{{ $term->schlyear }}">
                                        <input type="hidden" name="semester" value="{{ $term->semester }}">

                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Course Year&amp;Section: <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" name="class_id" required>
                                                    <option value="" selected disabled>--Select--</option>
                                                    @foreach ($classSections as $class)
                                                        <option value="{{ $class->id }}">{{ $class->course }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-7">
                                                <label class="form-label fw-semibold">Program Name</label>
                                                <input type="text" class="form-control form-control-sm js-program-name" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Total Units</label>
                                                <input type="text" class="form-control form-control-sm js-total-units" readonly>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Student Level: <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" name="studLevel" required>
                                                    <option value="" disabled @selected(! $defaults['level'])>--Select--</option>
                                                    @foreach ($levels as $level)
                                                        <option value="{{ $level->id }}" @selected($level->id == $defaults['level'])>{{ $level->studLevel }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Year Level</label>
                                                <input type="text" class="form-control form-control-sm js-year-level" readonly>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Scholarship: <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" name="studSch" required>
                                                    <option value="" disabled @selected(! $defaults['scholarship'])>--SELECT SCHOLARSHIP--</option>
                                                    @foreach ($scholarships as $scholarship)
                                                        <option value="{{ $scholarship->id }}" @selected($scholarship->id == $defaults['scholarship'])>{{ $scholarship->scholar_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Major: <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" name="studMajor" required>
                                                    @foreach ($majorMinors as $item)
                                                        <option value="{{ $item->submamiID }}" @selected($item->submamiID == $defaults['majorMinor'])>{{ $item->submamiName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Minor: <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" name="studMinor" required>
                                                    @foreach ($majorMinors as $item)
                                                        <option value="{{ $item->submamiID }}" @selected($item->submamiID == $defaults['majorMinor'])>{{ $item->submamiName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Status, Type and Transferee/Shiftee change the fees --}}
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Status: <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" name="studStatus" required>
                                                    @foreach ($statuses as $status)
                                                        <option value="{{ $status->id }}">{{ $status->studentStatName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Type: <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" name="studType" required>
                                                    <option value="" selected disabled>--Select--</option>
                                                    @foreach ($types as $type)
                                                        <option value="{{ $type->id }}">{{ $type->studentTypeName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Transferee/Shiftee: <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" name="transferee" required>
                                                    @foreach ($shiftTrans as $item)
                                                        <option value="{{ $item->id }}" @selected($item->id == $defaults['transferee'])>{{ $item->studentShiftTransDesc }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">4P's Beneficiaries: <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" name="fourPs" required>
                                                    <option value="" selected disabled>--Select--</option>
                                                    <option value="0">NO</option>
                                                    <option value="1">YES</option>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Subjects: filled from the section's template; more can be added / removed --}}
                            <div class="card card-animate mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center pt-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-book"></i> Student Subjects Section
                                    </h6>
                                    <button type="button" class="btn btn-info btn-sm text-white js-lockable" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                                        <i class="ti ti-plus"></i> Add Subject
                                    </button>
                                </div>
                                <div class="card-body card-body-bg-color table-responsive">
                                    <table id="subjectTable" class="table table-striped table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>SubjCode</th>
                                                <th>Subject Name</th>
                                                <th>Descriptive Title</th>
                                                <th class="text-end">Credit</th>
                                                <th class="text-end">LecFee</th>
                                                <th class="text-end">LabFee</th>
                                                <th class="text-end">DevFee</th>
                                                <th class="text-center">ITSubj</th>
                                                <th width="5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="js-empty"><td colspan="9" class="text-center text-muted py-3">Select a Course Year &amp; Section to load its subjects.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Fees: computed by the server when Assess is clicked --}}
                            <div class="card card-animate mb-3">
                                <div class="card-header pt-3">
                                    <h6 class="card-title">
                                        <i class="ti ti-receipt"></i> Student Appraisal Fees Section
                                    </h6>
                                </div>
                                <div class="card-body card-body-bg-color table-responsive">
                                    <table id="feeTable" class="table table-striped table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Fund</th>
                                                <th>Account</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="js-empty"><td colspan="3" class="text-center text-muted py-3">Click Assess to compute the fees.</td></tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold">
                                                <td colspan="2" class="text-end">Total</td>
                                                <td class="text-end js-fee-total">0.00</td>
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
                                            <i class="ti ti-settings"></i> Enrollment Action Buttons
                                        </h6>
                                    </div>
                                    <div class="card-body card-body-bg-color d-grid gap-2">
                                        <a href="{{ route('employee.enrollment.enroll.index') }}" wire:navigate class="btn btn-secondary btn-sm">Enroll New</a>
                                        <button type="button" class="btn btn-success btn-sm js-lockable" id="assessButton">Assess</button>
                                        {{-- Enabled after a successful Assess; any change of section, subjects, status, type or transferee needs a new Assess --}}
                                        <button type="button" class="btn btn-success btn-sm" id="saveButton" disabled>Save</button>
                                        <button type="button" class="btn btn-outline-success btn-sm" id="printRfButton" disabled title="Available after saving">Print RF</button>
                                    </div>
                                </div>

                                <div class="card card-animate">
                                    <div class="card-body card-body-bg-color">
                                        <div class="row g-2 small">
                                            <div class="col-6">Tuition: <input type="text" class="form-control form-control-sm js-total-lec" readonly></div>
                                            <div class="col-6">Lab Fee: <input type="text" class="form-control form-control-sm js-total-lab" readonly></div>
                                            <div class="col-12">Dev Fee: <input type="text" class="form-control form-control-sm js-total-dev" readonly></div>
                                            <div class="col-12">IT Subject: <input type="text" class="form-control form-control-sm js-it-subject" readonly></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-employee.pdf-modal id="registrationFormModal" title="Registration Form" />

        {{-- Add Subject: subjects offered in the campus this term --}}
        <div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSubjectModalLabel"><i class="fas fa-plus"></i> Add New Subject</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between">
                            <label class="form-label fw-semibold">Subjects: <span class="text-danger">*</span></label>
                            <small class="text-muted">Total of {{ $offeredSubjects->count() }} subjects offered this semester</small>
                        </div>
                        <select class="form-select form-select-sm" id="subjectSelect">
                            <option value="">--Select--</option>
                            @foreach ($offeredSubjects as $subject)
                                <option value="{{ $subject['id'] }}" data-row='@json($subject)'>{{ $subject['name'] }} &mdash; {{ $subject['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ti ti-restore"></i> Close</button>
                        <button type="button" class="btn btn-success" id="addSubjectButton"><i class="ti ti-plus"></i> Add</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/enrollment/enroll-form.js" />
@endpush
