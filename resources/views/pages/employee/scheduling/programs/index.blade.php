@extends('layouts.master_empscheduling')

@section('title')
    CISS - Scheduling | Programs
@endsection

@section('workspace')
    {{-- URLs for scheduling/programs.js (__ID__ is replaced with the program's id) --}}
    <div id="schedPrograms"
        data-list-url="{{ route('employee.scheduling.programs.list') }}"
        data-update-url="{{ route('employee.scheduling.programs.update', '__ID__') }}"
        data-departments-url="{{ route('employee.scheduling.lookup.departments') }}">

        <div class="row">
            <div class="col-12">
                <div class="mb-6">
                    @include('pages.employee.scheduling.partials.header', [
                        'page' => 'Programs',
                        'title' => 'Programs',
                        'description' => 'Every program: its college, department, level and the campuses that offer it.',
                    ])

                    <div class="card card-animate">
                        <div class="card-header pt-3">
                            <h6 class="card-title"><i class="ti ti-server"></i> List of Programs</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="schedProgramsTable" class="table table-hover" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Program Code</th>
                                            <th>Acronym</th>
                                            <th>Program Name</th>
                                            <th>Campuses</th>
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

        <x-employee.form-modal id="editProgramModal" title="Edit Program" form-id="editProgramForm" url="update-url" method="PUT" size="xl">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">College: <span class="text-danger">*</span></label>
                    <select class="form-control form-control-sm js-college" name="progCollege" required>
                        @foreach ($colleges as $abbr => $name)
                            <option value="{{ $abbr }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Department: <span class="text-danger">*</span></label>
                    <select class="form-control form-control-sm js-department" name="progDep" required></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Program Code:</label>
                    <input type="text" class="form-control form-control-sm js-code" readonly tabindex="-1">
                    <small class="text-muted js-code-note"></small>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Program Name: <span class="text-danger">*</span></label>
                    <input type="text" name="progName" class="form-control form-control-sm" maxlength="255" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Program Acronym: <span class="text-danger">*</span></label>
                    <input type="text" name="progAcronym" class="form-control form-control-sm" maxlength="100" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Program Level: <span class="text-danger">*</span></label>
                    <select name="progLev" class="form-control form-control-sm" required>
                        <option value="" disabled>--Select--</option>
                        @foreach ($levels as $id => $level)
                            <option value="{{ $id }}">{{ $level }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Campuses: <span class="text-danger">*</span></label>
                    @include('pages.employee.scheduling.partials.campus-checkboxes', ['campuses' => $campuses])
                </div>
            </div>
        </x-employee.form-modal>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/scheduling/common.js" />
    <x-script src="js/employee/scheduling/programs.js" />
@endpush
