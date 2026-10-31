@extends('layouts.master_empkiosk')

@section('title')
    CISS - Kiosk | Bulk Generation
@endsection

@section('workspace')
    {{-- URLs for kiosk/bulk.js (the classes table via CissCrud; a class's students; password generation) --}}
    <div id="kioskBulk"
        data-list-url="{{ route('employee.kiosk.bulk.list') }}"
        data-students-url="{{ route('employee.kiosk.bulk.students') }}"
        data-generate-url="{{ route('employee.kiosk.bulk.generate') }}">

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
                                <li class="breadcrumb-item mt-1">Kiosk</li>
                                <li class="breadcrumb-item active mt-1">Bulk Generation</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Student Kiosk Bulk Registration</h1>
                            <p class="text-muted small mb-0">Generate the kiosk passwords of a whole class and print them for the students.</p>
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
                                    <form id="kioskBulkSearchForm" autocomplete="off">
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

                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Academic Year: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="schlyear" required>
                                                    @foreach ($schoolYears as $schoolYear)
                                                        <option value="{{ $schoolYear }}">{{ $schoolYear }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Semester: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="semester" required>
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach ($semesters as $semester)
                                                        <option value="{{ $semester }}">{{ \App\Models\SettingDB\ConfigureCurrent::SEMESTERS[$semester] }}</option>
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
                                <div class="card-header pt-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-server"></i> Classes and their Kiosk Accounts
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="kioskBulkTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th width="30%">Program Name</th>
                                                    <th>Acronym</th>
                                                    <th>Year & Section</th>
                                                    <th>No. of Stud</th>
                                                    <th>Male</th>
                                                    <th>Female</th>
                                                    <th>With Kiosk Account</th>
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

        {{-- A class's students and the generate buttons (kiosk/bulk.js) --}}
        <div class="modal fade" id="kioskBulkStudentsModal" tabindex="-1" aria-labelledby="kioskBulkStudentsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="kioskBulkStudentsModalLabel">Students of <span class="js-class-name"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn btn-success btn-sm js-generate" data-mode="missing">
                                <i class="fas fa-key"></i> Generate for students without an account (<span class="js-missing-count">0</span>)
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm js-generate" data-mode="all">
                                <i class="fas fa-rotate"></i> Reset all passwords (<span class="js-all-count">0</span>)
                            </button>
                        </div>
                        <p class="small text-muted mb-2">The new passwords open as a PDF to print. They are shown only there, so print or save it before closing.</p>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Student ID No.</th>
                                        <th>Name</th>
                                        <th>Gender</th>
                                        <th>Kiosk Account</th>
                                    </tr>
                                </thead>
                                <tbody class="js-student-rows"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <x-employee.pdf-modal id="kioskPasswordsPdfModal" title="Kiosk Passwords" />
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/kiosk/bulk.js" />
@endpush
