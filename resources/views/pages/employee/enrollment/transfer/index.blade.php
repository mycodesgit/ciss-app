@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Transferred Student
@endsection

@section('workspace')
    {{-- URLs for enrollment/student-transfer.js (the list via CissCrud) --}}
    <div id="studentTransfers"
        data-list-url="{{ route('employee.enrollment.transfers.list') }}"
        data-lookup-url="{{ route('employee.enrollment.transfers.lookup') }}"
        data-store-url="{{ route('employee.enrollment.transfers.store') }}">

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
                                <li class="breadcrumb-item active mt-1">Transferred Student</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Transferred Student</h1>
                            <p class="text-muted small mb-0">Transfer a student to another campus. The student's records stay visible to both campuses.</p>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        {{-- Step 1: find the student; step 2: pick the campus and confirm --}}
                        <div class="col-md-4">
                            <div class="card card-animate">
                                <div class="card-header pt-3">
                                    <h6 class="card-title">
                                        <i class="ti ti-transfer"></i> Transfer a Student
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="transferLookupForm" autocomplete="off">
                                        <label class="form-label fw-semibold">Student ID Number: <span class="text-danger">*</span></label>
                                        {{-- Typed as 2024-0001-K (crud.js formats it) --}}
                                        <input type="text" name="stud_id" class="form-control form-control-sm js-student-id" placeholder="0000-0000-X" maxlength="11" required autofocus>
                                        <div class="form-text small">Enter the student's ID, then find the record to transfer.</div>
                                        <div class="d-grid mt-3">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="ti ti-search"></i> Find Student
                                            </button>
                                        </div>
                                    </form>

                                    {{-- Shown after Find (student-transfer.js) --}}
                                    <form id="transferForm" class="d-none mt-3" autocomplete="off">
                                        <input type="hidden" name="stud_id">
                                        <dl class="row small mb-2">
                                            <dt class="col-5">Name</dt><dd class="col-7 js-name"></dd>
                                            <dt class="col-5">Campus now</dt><dd class="col-7 js-from"></dd>
                                            <dt class="col-5">All campuses</dt><dd class="col-7 js-campuses"></dd>
                                            <dt class="col-5">Last enrollment</dt><dd class="col-7 js-last"></dd>
                                        </dl>
                                        <div class="alert alert-warning small py-2 d-none js-enrolled-now">
                                            <i class="ti ti-alert-triangle"></i> Enrolled this term: the new campus is added to this enrollment too.
                                        </div>
                                        <label class="form-label fw-semibold">Transfer to: <span class="text-danger">*</span></label>
                                        <select name="tocampus" class="form-select form-select-sm" required></select>
                                        <div class="d-grid gap-2 mt-3">
                                            <button type="submit" class="btn btn-success btn-sm">Transfer</button>
                                            <button type="button" class="btn btn-light btn-sm js-cancel-transfer">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card card-animate">
                                <div class="card-header pt-3">
                                    <h6 class="card-title">
                                        <i class="ti ti-list"></i> List of Transferred Students
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive p-2">
                                        <table id="transfersTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Stud ID</th>
                                                    <th>Campus</th>
                                                    <th>Date Transferred</th>
                                                    <th>Transferred By</th>
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
    <x-script src="js/employee/enrollment/student-transfer.js" />
@endpush
