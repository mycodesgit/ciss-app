@extends('layouts.master_empscheduling')

@section('title')
    CISS - Scheduling | Classes Enrolled
@endsection

@section('workspace')
    {{-- URLs for scheduling/classes.js (__ID__ is replaced with the section's id) --}}
    <div id="schedClasses"
        data-list-url="{{ route('employee.scheduling.classes.list') }}"
        data-store-url="{{ route('employee.scheduling.classes.store') }}"
        data-update-url="{{ route('employee.scheduling.classes.update', '__ID__') }}"
        data-destroy-url="{{ route('employee.scheduling.classes.destroy', '__ID__') }}">

        <div class="row">
            <div class="col-12">
                <div class="mb-6">
                    @include('pages.employee.scheduling.partials.header', [
                        'page' => 'Classes Enrolled',
                        'title' => 'Classes Enrolled',
                        'description' => 'The sections (program, year & section) of a campus in a term.',
                    ])

                    @include('pages.employee.scheduling.partials.search-form', ['formId' => 'schedClassesSearchForm'])

                    <div class="card card-animate">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                            <h6 class="card-title mb-0"><i class="ti ti-server"></i> List of Classes Enrolled</h6>
                            <div class="d-flex align-items-center gap-2">
                                <small class="text-muted js-term-shown"></small>
                                <button type="button" class="btn btn-success btn-sm text-light text-nowrap" data-bs-toggle="modal" data-bs-target="#addClassModal">
                                    <i class="fas fa-plus"></i> Add New
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="schedClassesTable" class="table table-hover" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Program</th>
                                            <th>Year & Section</th>
                                            <th>Est. No. of Students</th>
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

        @foreach (['add' => ['Add Section', 'store-url', 'POST'], 'edit' => ['Edit Section', 'update-url', 'PUT']] as $mode => [$title, $url, $method])
            <x-employee.form-modal :id="$mode . 'ClassModal'" :title="$title" :form-id="$mode . 'ClassForm'" :url="$url" :method="$method">
                {{-- Add: the searched campus and term (classes.js); the server checks them --}}
                <input type="hidden" name="campus">
                <input type="hidden" name="schlyear">
                <input type="hidden" name="semester">
                <div class="row g-3">
                    <div class="col-md-12">
                        <small class="text-muted js-modal-term"></small>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Program: <span class="text-danger">*</span></label>
                        {{-- The searched campus's programs (filled by classes.js from the list response) --}}
                        <select class="form-control form-control-sm js-programs" name="progCode" required></select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Year & Section: <span class="text-danger">*</span></label>
                        <input type="text" name="classSection" class="form-control form-control-sm text-uppercase" placeholder="e.g. 1-A" maxlength="40" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Est. Number of Students: <span class="text-danger">*</span></label>
                        <input type="number" name="classno" class="form-control form-control-sm" min="0" max="500" required>
                    </div>
                    @if ($mode === 'add' && $addOns)
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Add On:</label>
                            <select class="form-control form-control-sm" name="addOn">
                                <option value="">None</option>
                                @foreach ($addOns as $addOn)
                                    <option value="{{ $addOn }}">{{ $addOn }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </x-employee.form-modal>
        @endforeach
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/scheduling/common.js" />
    <x-script src="js/employee/scheduling/classes.js" />
@endpush
