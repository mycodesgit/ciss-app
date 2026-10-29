@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Edit Duplicate Fees
@endsection

@section('workspace')
    {{-- URLs for enrollment/enroll.js (same search page script as Enroll Student / Edit Enrollment) --}}
    <div id="enrollStudent"
        data-find-url="{{ route('employee.enrollment.dup-fees.find') }}"
        data-queue-status-url="{{ route('employee.enrollment.queue.status') }}"
        data-queue-category-url="{{ route('employee.enrollment.queue.category') }}"
        data-queue-next-url="{{ route('employee.enrollment.queue.next') }}"
        data-queue-call-url="{{ route('employee.enrollment.queue.call') }}"
        data-pre-enrolled-url="{{ route('employee.enrollment.queue.pre-enrolled') }}">

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
                                <li class="breadcrumb-item active mt-1">Edit Duplicate Fees</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Edit Duplicate Fees</h1>
                            <p class="text-muted small mb-0">Edit Duplicate Student Fees.</p>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div @class(['col-md-9' => $hasQueueCounter && $queueOn, 'col-md-12' => ! ($hasQueueCounter && $queueOn)])>
                            <div class="card card-animate mb-3">
                                <div class="card-header pt-3">
                                    <h6 class="card-title">
                                        <i class="ti ti-search"></i> Search to show data
                                    </h6>
                                </div>
                                <div class="card-body">
                                    {{-- Checked by AJAX; when the enrollment is found, its fees open (no page reload) --}}
                                    <form id="enrollSearchForm" autocomplete="off">
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
                                                {{-- Typed as 2024-0001-K (enroll.js formats it) --}}
                                                <input type="text" name="stud_id" class="form-control form-control-sm js-student-id" placeholder="0000-0000-X" maxlength="11" required autofocus>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">School Year: <span class="text-danger">*</span></label>
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
                                                        <i class="ti ti-search"></i> OK
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            @if ($queueOn)
                                @include('pages.employee.enrollment.studenroll.partials.pre-enrolled-list')
                            @endif
                        </div>

                        @if ($hasQueueCounter && $queueOn)
                            <div class="col-md-3">
                                @include('pages.employee.enrollment.studenroll.partials.queue-panel')
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/enrollment/enroll.js" />
@endpush
