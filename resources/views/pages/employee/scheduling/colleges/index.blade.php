@extends('layouts.master_empscheduling')

@section('title')
    CISS - Scheduling | Colleges
@endsection

@section('workspace')
    {{-- URLs for scheduling/colleges.js (__ID__ is replaced with the college's id) --}}
    <div id="schedColleges"
        data-list-url="{{ route('employee.scheduling.colleges.list') }}"
        data-update-url="{{ route('employee.scheduling.colleges.update', '__ID__') }}">

        <div class="row">
            <div class="col-12">
                <div class="mb-6">
                    @include('pages.employee.scheduling.partials.header', [
                        'page' => 'Colleges',
                        'title' => 'Colleges',
                        'description' => 'The academic colleges and the campuses each one is in.',
                    ])

                    <div class="card card-animate">
                        <div class="card-header pt-3">
                            <h6 class="card-title"><i class="ti ti-server"></i> List of Colleges</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="schedCollegesTable" class="table table-hover" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Acronym</th>
                                            <th>College Name</th>
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

        <x-employee.form-modal id="editCollegeModal" title="Edit College" form-id="editCollegeForm" url="update-url" method="PUT">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Acronym:</label>
                    <input type="text" class="form-control js-college-abbr" readonly tabindex="-1">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">College Name:</label>
                    <input type="text" class="form-control js-college-name" readonly tabindex="-1">
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
    <x-script src="js/employee/scheduling/colleges.js" />
@endpush
