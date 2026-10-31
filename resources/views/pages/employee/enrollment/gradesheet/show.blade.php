{{-- A subject's gradesheet, in the Enrollment or the NSTP module ($module: name, layout; $routeName from the controller) --}}
@extends($module['layout'])

@section('title')
    CISS - {{ $module['name'] }} | Gradesheet
@endsection

@section('workspace')
    {{-- URLs and grade codes for enrollment/gradesheet.js (the rows are loaded by AJAX and re-drawn after every save) --}}
    <div id="gradesheet"
        data-students-url="{{ route("{$routeName}.students", $subject->id) }}"
        data-submit-url="{{ route("{$routeName}.submit", $subject->id) }}"
        data-grade-codes='@json($gradeCodes)'>

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
                                <li class="breadcrumb-item mt-1">{{ $module['name'] }}</li>
                                <li class="breadcrumb-item mt-1">
                                    <a href="{{ route("{$routeName}.index") }}" wire:navigate>Gradesheet</a>
                                </li>
                                <li class="breadcrumb-item active mt-1"><strong>{{ $subject->sub_name }} {{ $subject->subSec }}</strong></li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Student Gradesheet</h1>
                            <p class="text-muted small mb-0">{{ $subject->sub_name }} &mdash; {{ $subject->sub_title }} &middot; {{ $subject->subUnit }} unit(s)</p>
                        </div>
                        <div class="btn btn-light">
                            <i class="ti ti-circle-filled text-success"></i> Selected:
                            <i class="ti ti-calendar"></i> {{ $term->schlyear }} - {{ $term->semester_name }}
                        </div>
                    </div>

                    <div class="card card-animate">
                        <div class="card-header d-flex justify-content-between align-items-center pt-3">
                            <h6 class="card-title mb-0">
                                <i class="ti ti-server"></i> List of students | <strong>{{ $subject->sub_name }} {{ $subject->subSec }}</strong>
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <small class="text-muted js-pending-note"></small>
                                {{-- Enabled while there are encoded (not yet submitted) grades --}}
                                <button type="button" class="btn btn-success btn-sm text-light" id="submitGradesButton" disabled>Submit Grades</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="gradesheetTable" class="table table-bordered text-nowrap align-middle mb-0" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th style="min-width: 120px;">Final Grade</th>
                                            <th style="min-width: 120px;">Completion</th>
                                            <th>Equivalent</th>
                                            <th>Unit</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="8" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-2"></span> Loading students...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit Grades / Completion of a submitted grade: needs the campus grades password (checked by the server) --}}
        <div class="modal fade" id="unlockGradeModal" tabindex="-1" aria-labelledby="unlockGradeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="unlockGradeModalLabel"><i class="fas fa-info-circle"></i> Confirmation</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="unlockGradeForm" autocomplete="off">
                        <div class="modal-body">
                            <p class="mb-2 js-unlock-question"></p>
                            <label class="form-label small"><span class="badge bg-warning text-dark">Enter the grades password here</span></label>
                            <input type="password" name="password" class="form-control form-control-sm" required autocomplete="new-password">
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="submit" class="btn btn-success">Yes</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">No</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/enrollment/gradesheet.js" />
@endpush
