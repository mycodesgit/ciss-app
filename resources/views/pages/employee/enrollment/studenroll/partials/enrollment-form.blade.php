{{--
    Enrollment form cards (Enroll Student and Edit Enrollment): student, course, subjects, fees + Add Subject modal.
    Only choices are sent; program, year / section, units and fees are set by the server. Script: enroll-form.js
    Needs: $student, $term, the option lists, $selected (chosen values), $classInfo (programName, yearLevel)
    and $savedFees (Edit Enrollment: the saved appraisal shown until the next Assess).
--}}
@php
    $savedFees ??= collect();
@endphp

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

{{-- Course --}}
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
                        <option value="" disabled @selected(! $selected['class_id'])>--Select--</option>
                        @foreach ($classSections as $class)
                            <option value="{{ $class->id }}" @selected($class->id == $selected['class_id'])>{{ $class->course }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-7">
                    <label class="form-label fw-semibold">Program Name</label>
                    <input type="text" class="form-control form-control-sm js-program-name" value="{{ $classInfo['programName'] ?? '' }}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Total Units</label>
                    <input type="text" class="form-control form-control-sm js-total-units" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Student Level: <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="studLevel" required>
                        <option value="" disabled @selected(! $selected['studLevel'])>--Select--</option>
                        @foreach ($levels as $level)
                            <option value="{{ $level->id }}" @selected($level->id == $selected['studLevel'])>{{ $level->studLevel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Year Level</label>
                    <input type="text" class="form-control form-control-sm js-year-level" value="{{ $classInfo['yearLevel'] ?? '' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Scholarship: <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="studSch" required>
                        <option value="" disabled @selected(! $selected['studSch'])>--SELECT SCHOLARSHIP--</option>
                        @foreach ($scholarships as $scholarship)
                            <option value="{{ $scholarship->id }}" @selected($scholarship->id == $selected['studSch'])>{{ $scholarship->scholar_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Major: <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="studMajor" required>
                        <option value="" disabled @selected(! $selected['studMajor'])>--Select--</option>
                        @foreach ($majorMinors as $item)
                            <option value="{{ $item->submamiID }}" @selected($item->submamiID == $selected['studMajor'])>{{ $item->submamiName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Minor: <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="studMinor" required>
                        <option value="" disabled @selected(! $selected['studMinor'])>--Select--</option>
                        @foreach ($majorMinors as $item)
                            <option value="{{ $item->submamiID }}" @selected($item->submamiID == $selected['studMinor'])>{{ $item->submamiName }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status, Type and Transferee/Shiftee change the fees --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status: <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="studStatus" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}" @selected($status->id == $selected['studStatus'])>{{ $status->studentStatName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Type: <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="studType" required>
                        <option value="" disabled @selected(! $selected['studType'])>--Select--</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" @selected($type->id == $selected['studType'])>{{ $type->studentTypeName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Transferee/Shiftee: <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="transferee" required>
                        @foreach ($shiftTrans as $item)
                            <option value="{{ $item->id }}" @selected($item->id == $selected['transferee'])>{{ $item->studentShiftTransDesc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">4P's Beneficiaries: <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="fourPs" required>
                        <option value="" disabled @selected($selected['fourPs'] === null)>--Select--</option>
                        <option value="0" @selected($selected['fourPs'] === '0')>NO</option>
                        <option value="1" @selected($selected['fourPs'] === '1')>YES</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Subjects: the section's template subjects; more can be added / removed --}}
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

{{-- Fees: computed by the server when Assess is clicked (Edit Enrollment shows the saved ones until then) --}}
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
                @forelse ($savedFees as $fee)
                    <tr>
                        <td>{{ $fee->fundID }}</td>
                        <td>{{ $fee->account }}</td>
                        <td class="text-end">{{ number_format((float) $fee->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr class="js-empty"><td colspan="3" class="text-center text-muted py-3">Click Assess to compute the fees.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td colspan="2" class="text-end">{{ $savedFees->isNotEmpty() ? 'Saved total' : 'Total' }}</td>
                    <td class="text-end js-fee-total">{{ number_format($savedFees->sum(fn ($fee) => (float) $fee->amount), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

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
