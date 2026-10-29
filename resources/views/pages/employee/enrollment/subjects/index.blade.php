@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Subjects
@endsection

@section('workspace')
    {{-- URLs for enrollment/subjects.js (table + Add / Edit forms via CissCrud; __ID__ = the subject id) --}}
    <div id="subjects"
        data-list-url="{{ route('employee.enrollment.subjects.list') }}"
        data-store-url="{{ route('employee.enrollment.subjects.store') }}"
        data-update-url="{{ route('employee.enrollment.subjects.update', '__ID__') }}"
        data-status-url="{{ route('employee.enrollment.subjects.status', '__ID__') }}"
        data-next-code-url="{{ route('employee.enrollment.subjects.next-code') }}">

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
                                <li class="breadcrumb-item active mt-1">Subjects</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Subjects</h1>
                            <p class="text-muted small mb-0">The subjects master list. Codes are assigned when saving; deleted subjects are only set Inactive.</p>
                        </div>
                    </div>

                    <div class="card card-animate">
                        <div class="card-header d-flex justify-content-between align-items-center pt-3">
                            <h6 class="card-title mb-0">
                                <i class="ti ti-book"></i> List of Subjects
                            </h6>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                                <i class="fas fa-plus"></i> Add New
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="subjectsTable" class="table table-hover" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Subject Code</th>
                                            <th>Subject Name</th>
                                            <th>Description</th>
                                            <th>Unit</th>
                                            <th>Status</th>
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

        <x-employee.form-modal id="addSubjectModal" title="Add Subject" form-id="addSubjectForm" url="store-url" size="xl">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">College: <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm js-code-part" name="college" required>
                        <option value="" selected disabled>--Select--</option>
                        @foreach ($colleges as $college)
                            <option value="{{ $college->college_abbr }}">{{ $college->college_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Department: <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm js-code-part" name="deptCod" required>
                        <option value="" selected disabled>--Select--</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->deptCod }}">{{ $department->deptName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Subject Code:</label>
                    {{-- Preview only: the server assigns the code when saving --}}
                    <input type="text" class="form-control form-control-sm js-code-preview" placeholder="Select college and department" readonly>
                </div>

                @include('pages.employee.enrollment.subjects.partials.detail-fields')
            </div>
        </x-employee.form-modal>

        {{-- The code, college and department can't change (offerings use the code) --}}
        <x-employee.form-modal id="editSubjectModal" title="Edit Subject" form-id="editSubjectForm" url="update-url" method="PUT" size="xl">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Subject Code:</label>
                    <input type="text" class="form-control form-control-sm js-edit-code" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">College:</label>
                    <input type="text" class="form-control form-control-sm js-edit-college" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Department:</label>
                    <input type="text" class="form-control form-control-sm js-edit-department" readonly>
                </div>

                @include('pages.employee.enrollment.subjects.partials.detail-fields')
            </div>
        </x-employee.form-modal>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/enrollment/subjects.js" />
@endpush
