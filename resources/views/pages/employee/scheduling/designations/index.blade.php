@extends('layouts.master_empscheduling')

@section('title')
    CISS - Scheduling | Faculty Designation
@endsection

@section('workspace')
    {{-- URLs for scheduling/designations.js (__ID__ is replaced with the designation's id) --}}
    <div id="schedDesignations"
        data-list-url="{{ route('employee.scheduling.designations.list') }}"
        data-store-url="{{ route('employee.scheduling.designations.store') }}"
        data-update-url="{{ route('employee.scheduling.designations.update', '__ID__') }}"
        data-destroy-url="{{ route('employee.scheduling.designations.destroy', '__ID__') }}">

        <div class="row">
            <div class="col-12">
                <div class="mb-6">
                    @include('pages.employee.scheduling.partials.header', [
                        'page' => 'Faculty Designation',
                        'title' => 'Faculty Designation',
                        'description' => 'The designations of a campus\'s faculty in a term, with their units.',
                    ])

                    @include('pages.employee.scheduling.partials.search-form', ['formId' => 'schedDesignationsSearchForm'])

                    <div class="card card-animate">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                            <h6 class="card-title mb-0"><i class="ti ti-users"></i> List of Faculty Designation</h6>
                            <div class="d-flex align-items-center gap-2">
                                <small class="text-muted js-term-shown"></small>
                                <button type="button" class="btn btn-success btn-sm text-light text-nowrap" data-bs-toggle="modal" data-bs-target="#addDesignationModal">
                                    <i class="fas fa-plus"></i> Add New
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="schedDesignationsTable" class="table table-hover" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Faculty</th>
                                            <th>Designation</th>
                                            <th>College</th>
                                            <th>Unit</th>
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

        @foreach (['add' => ['Add Designation', 'store-url', 'POST'], 'edit' => ['Edit Designation', 'update-url', 'PUT']] as $mode => [$title, $url, $method])
            <x-employee.form-modal :id="$mode . 'DesignationModal'" :title="$title" :form-id="$mode . 'DesignationForm'" :url="$url" :method="$method">
                {{-- Add: the searched campus and term (designations.js); the server checks them --}}
                <input type="hidden" name="campus">
                <input type="hidden" name="schlyear">
                <input type="hidden" name="semester">
                <div class="row g-3">
                    <div class="col-md-12">
                        <small class="text-muted js-modal-term"></small>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Faculty: <span class="text-danger">*</span></label>
                        {{-- The searched campus's faculty (filled by designations.js from the list response) --}}
                        <select class="form-control form-control-sm js-faculty" name="fac_id" required></select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">College: <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" name="facCollege" required>
                            <option value="" selected disabled>--Select--</option>
                            @foreach ($colleges as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Designation: <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" name="designation" required>
                            <option value="" selected disabled>--Select--</option>
                            @foreach ($designations as $group => $options)
                                <optgroup label="{{ $group }}">
                                    @foreach ($options as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Unit: <span class="text-danger">*</span></label>
                        <input type="number" name="dunit" class="form-control form-control-sm" min="0" max="99" required>
                    </div>
                </div>
            </x-employee.form-modal>
        @endforeach
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/scheduling/common.js" />
    <x-script src="js/employee/scheduling/designations.js" />
@endpush
