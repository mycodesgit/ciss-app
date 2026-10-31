@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Gradesheet Logbook
@endsection

@section('workspace')
    {{-- URLs for enrollment/gradesheet-logbook.js (the table via CissCrud, the logbook PDF) --}}
    <div id="gradesheetLogbook"
        data-list-url="{{ route('employee.enrollment.gradesheet-logbook.list') }}"
        data-pdf-url="{{ route('employee.enrollment.gradesheet-logbook.pdf') }}">

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
                                <li class="breadcrumb-item active mt-1">Gradesheet Logbook</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Gradesheet Logbook</h1>
                            <p class="text-muted small mb-0">When the faculty of a college submitted their gradesheets, and the logbook to sign as PDF.</p>
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
                                    <form id="gradesheetLogbookSearchForm" autocomplete="off">
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

                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">School Year: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="schlyear" required>
                                                    @foreach ($schoolYears as $schoolYear)
                                                        <option value="{{ $schoolYear }}">{{ $schoolYear }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Semester: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="semester" required>
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach ($semesters as $semester)
                                                        <option value="{{ $semester }}">{{ \App\Models\SettingDB\ConfigureCurrent::SEMESTERS[$semester] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">College: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="college" required>
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach ($colleges as $abbr => $name)
                                                        <option value="{{ $abbr }}">{{ $name }}</option>
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
                                <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center pt-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-address-book"></i> Submitted Gradesheets
                                    </h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-muted js-search-shown"></small>
                                        {{-- Enabled after a search: the logbook PDF of the searched college and term --}}
                                        <button type="button" class="btn btn-danger btn-sm" id="gradesheetLogbookPdfButton" disabled>
                                            <i class="fas fa-file-pdf"></i> Logbook PDF
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="gradesheetLogbookTable" class="table table-hover text-nowrap" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Term</th>
                                                    <th>Faculty</th>
                                                    <th>Subject</th>
                                                    <th>Subject Title</th>
                                                    <th>Curr/Yr/Sec</th>
                                                    <th>College</th>
                                                    <th>Date Submitted</th>
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

        <x-employee.pdf-modal id="gradesheetLogbookPdfModal" title="Gradesheet Logbook" />
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/enrollment/gradesheet-logbook.js" />
@endpush
