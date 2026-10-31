@extends('layouts.master_empscheduling')

@section('title')
    CISS - Scheduling | Faculty List
@endsection

@section('workspace')
    {{-- URLs for scheduling/faculty.js (__ID__ is replaced with the faculty member's id) --}}
    <div id="schedFaculty"
        data-list-url="{{ route('employee.scheduling.faculty.list') }}"
        data-store-url="{{ route('employee.scheduling.faculty.store') }}"
        data-update-url="{{ route('employee.scheduling.faculty.update', '__ID__') }}"
        data-assign-url="{{ route('employee.scheduling.faculty.assign', '__ID__') }}"
        data-search-url="{{ route('employee.scheduling.faculty.search') }}"
        data-departments-url="{{ route('employee.scheduling.lookup.departments') }}"
        data-major-department="{{ $majorDepartment }}">

        <div class="row">
            <div class="col-12">
                <div class="mb-6">
                    @include('pages.employee.scheduling.partials.header', [
                        'page' => 'Faculty List',
                        'title' => 'Faculty List',
                        'description' => 'The faculty of a campus: add a newly hired one, or assign an existing one from another campus.',
                    ])

                    @include('pages.employee.scheduling.partials.search-form', ['formId' => 'schedFacultySearchForm', 'withTerm' => false])

                    <div class="card card-animate">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                            <h6 class="card-title mb-0"><i class="ti ti-users"></i> List of Faculty</h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-success btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#assignFacultyModal">
                                    <i class="fas fa-user-check"></i> Assign Existing Faculty
                                </button>
                                <button type="button" class="btn btn-success btn-sm text-light text-nowrap" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
                                    <i class="fas fa-user-plus"></i> Add Newly Hired
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="schedFacultyTable" class="table table-hover" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Salutation</th>
                                            <th>College</th>
                                            <th>Department</th>
                                            <th>Rank</th>
                                            <th>Campuses</th>
                                            <th>Active Campus</th>
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

        @foreach (['add' => ['Add Newly Hired Faculty', 'store-url', 'POST'], 'edit' => ['Edit Faculty', 'update-url', 'PUT']] as $mode => [$title, $url, $method])
            <x-employee.form-modal :id="$mode . 'FacultyModal'" :title="$title" :form-id="$mode . 'FacultyForm'" :url="$url" :method="$method" size="lg">
                {{-- Add: the searched campus (faculty.js); the server checks it --}}
                <input type="hidden" name="campus">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">College: <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm js-college" name="faccollege" required>
                            <option value="" selected disabled>--Select--</option>
                            @foreach ($colleges as $abbr => $name)
                                <option value="{{ $abbr }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Department: <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm js-department" name="facdept" required></select>
                    </div>
                    <div class="col-md-6 js-major d-none">
                        <label class="form-label fw-semibold">Major: <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" name="deptmajor">
                            <option value="">--Select Major--</option>
                            @foreach ($majors as $major)
                                <option value="{{ $major }}">{{ $major }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-100 m-0"></div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Last Name: <span class="text-danger">*</span></label>
                        <input type="text" name="lname" class="form-control form-control-sm js-capitalize" maxlength="100" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">First Name: <span class="text-danger">*</span></label>
                        <input type="text" name="fname" class="form-control form-control-sm js-capitalize" maxlength="100" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Middle Name:</label>
                        <input type="text" name="mname" class="form-control form-control-sm js-capitalize" maxlength="100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ext:</label>
                        <input type="text" name="ext" class="form-control form-control-sm" maxlength="10" placeholder="Jr., Sr., III">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Prefix: <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" name="prefix" required>
                            <option value="" selected disabled>--Select--</option>
                            @foreach ($prefixes as $id => $prefix)
                                <option value="{{ $id }}">{{ $prefix }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Suffix:</label>
                        <select class="form-control form-control-sm" name="suffix">
                            <option value="">None</option>
                            @foreach ($suffixes as $suffix)
                                <option value="{{ $suffix }}">{{ $suffix }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email:</label>
                        <input type="email" name="email" class="form-control form-control-sm" maxlength="255">
                    </div>
                    @if ($mode === 'edit')
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Academic Rank:</label>
                            <select class="form-control form-control-sm" name="rank">
                                <option value="">None</option>
                                @foreach ($ranks as $rank)
                                    <option value="{{ $rank }}">{{ $rank }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </x-employee.form-modal>
        @endforeach

        <x-employee.form-modal id="assignFacultyModal" title="Assign Existing Faculty" form-id="assignFacultyForm" url="assign-url" method="PUT">
            <input type="hidden" name="campus">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Faculty from another campus: <span class="text-danger">*</span></label>
                    <select class="form-control form-control-sm js-existing-faculty" required></select>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Assign to Campus:</label>
                    <input type="text" class="form-control form-control-sm js-assign-campus" readonly tabindex="-1">
                    <small class="text-muted">This campus is added to the faculty member's campuses and becomes their active campus.</small>
                </div>
            </div>
        </x-employee.form-modal>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/scheduling/common.js" />
    <x-script src="js/employee/scheduling/faculty.js" />
@endpush
