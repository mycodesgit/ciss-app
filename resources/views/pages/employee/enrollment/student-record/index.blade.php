@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Student Record
@endsection

@section('workspace')
    {{-- URL for enrollment/student-record.js --}}
    <div id="studentRecord" data-find-url="{{ route('employee.enrollment.student-record.find') }}">

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
                                <li class="breadcrumb-item active mt-1">Student Record</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Student Record</h1>
                            <p class="text-muted small mb-0">A student's whole academic record: every subject per term, as a PDF and with the computation.</p>
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
                                    {{-- Searching loads the record below (no page reload) --}}
                                    <form id="studentRecordSearchForm" autocomplete="off">
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
                                                <label class="form-label fw-semibold">Student ID Number: <span class="text-danger">*</span></label>
                                                {{-- Typed as 2024-0001-K (crud.js formats it) --}}
                                                <input type="text" name="stud_id" class="form-control form-control-sm js-student-id" placeholder="0000-0000-X" maxlength="11" required autofocus>
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

                        {{-- Filled by student-record.js after a search --}}
                        <div class="col-md-12 d-none js-result">
                            <div class="card">
                                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-book-2"></i>
                                        <span class="js-student-id-shown"></span> &middot; <span class="js-student-name"></span>
                                    </h6>
                                    <small class="text-muted">
                                        <span class="js-student-program"></span> &middot; <span class="js-student-campus"></span>
                                    </small>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-pills bg-light p-2 rounded-2 d-inline-flex mb-3" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#studentRecordPdfTab" type="button" role="tab">
                                                <i class="fas fa-file-pdf"></i> Student Record PDF
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#studentRecordComputationTab" type="button" role="tab">
                                                <i class="fas fa-calculator"></i> Computation
                                            </button>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="studentRecordPdfTab" role="tabpanel">
                                            <iframe class="w-100 border rounded js-record-pdf" style="height: 75vh;" title="Student Record"></iframe>
                                        </div>
                                        <div class="tab-pane fade" id="studentRecordComputationTab" role="tabpanel">
                                            <p class="small text-muted">
                                                Summation = grade (as encoded) &times; credits. Each term's average is the summation over the credits;
                                                grades that are not numbers and NSTP subjects (credits in parentheses) are left out.
                                            </p>
                                            <div class="table-responsive">
                                                <table id="studentRecordTable" class="table table-sm table-hover text-nowrap" style="width: 100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Student ID</th>
                                                            <th>School Year</th>
                                                            <th>Semester</th>
                                                            <th>Course</th>
                                                            <th>Subject</th>
                                                            <th>Descriptive Title</th>
                                                            <th>Final Grade</th>
                                                            <th>N Grade</th>
                                                            <th>Completion</th>
                                                            <th>Credit</th>
                                                            <th>Summation</th>
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
        </div>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/enrollment/student-record.js" />
@endpush
