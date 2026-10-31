@extends('layouts.master_empnstp')

@section('title')
    CISS - NSTP | {{ $component['label'] }} Students
@endsection

@section('workspace')
    {{-- URL, component and fixed HEI columns for nstp/students.js (the table, via CissCrud) --}}
    <div id="nstpStudents"
        data-list-url="{{ route('employee.nstp.students.list', $component['key']) }}"
        data-component="{{ $component['label'] }}"
        data-hei='@json($hei)'>

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
                                <li class="breadcrumb-item mt-1">NSTP</li>
                                <li class="breadcrumb-item active mt-1">{{ $component['label'] }} Students</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">{{ $component['label'] }} Students</h1>
                            <p class="text-muted small mb-0">Students enrolled in NSTP - {{ $component['name'] }}, in the CHED serial number list format.</p>
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
                                    <form id="nstpStudentsSearchForm" autocomplete="off">
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
                                <div class="card-header d-flex justify-content-between align-items-center pt-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-server"></i> List of NSTP - {{ $component['label'] }} Students
                                    </h6>
                                    <small class="text-muted js-search-shown"></small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive p-2">
                                        <table id="nstpStudentsTable" class="table table-hover text-nowrap" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>NO.</th>
                                                    <th>STUDID.</th>
                                                    <th>AWARD YEAR</th>
                                                    <th>NSTP PROGRAM</th>
                                                    <th>REGION</th>
                                                    <th>SERIAL NO.</th>
                                                    <th>LAST NAME</th>
                                                    <th>FIRST NAME</th>
                                                    <th>EXT. NAME</th>
                                                    <th>MIDDLE NAME</th>
                                                    <th>BIRTHDATE</th>
                                                    <th>SEX</th>
                                                    <th>STREET/BRGY</th>
                                                    <th>TOWN/CITY</th>
                                                    <th>PROVINCE</th>
                                                    <th>HEI NAME</th>
                                                    <th>INSTITUTIONAL CODE</th>
                                                    <th>TYPE</th>
                                                    <th>PROGRAM LEVEL</th>
                                                    <th>MAIN PROGRAM NAME</th>
                                                    <th>EMAIL</th>
                                                    <th>CONTACT NUMBER</th>
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
    <x-script src="js/employee/nstp/students.js" />
@endpush
