@extends('layouts.master_empscheduling')

@section('title')
    CISS - Scheduling | Subject Offered
@endsection

@section('workspace')
    {{-- URLs and options for scheduling/offerings.js (__ID__ = sub_offered id, __CODE__ = subject code) --}}
    <div id="schedOfferings"
        data-list-url="{{ route('employee.scheduling.offerings.list') }}"
        data-store-url="{{ route('employee.scheduling.offerings.store') }}"
        data-update-url="{{ route('employee.scheduling.offerings.update', '__ID__') }}"
        data-destroy-url="{{ route('employee.scheduling.offerings.destroy', '__ID__') }}"
        data-template-url="{{ route('employee.scheduling.offerings.template') }}"
        data-template-store-url="{{ route('employee.scheduling.offerings.template.store') }}"
        data-subjects-url="{{ route('employee.scheduling.lookup.subjects') }}"
        data-fees-url="{{ route('employee.scheduling.lookup.fees', '__CODE__') }}"
        data-ojt-options='@json($ojtOptions)'
        data-template-campuses='@json($templateCampuses)'
        data-max-students="{{ $maxStudents }}">

        <div class="row">
            <div class="col-12">
                <div class="mb-6">
                    @include('pages.employee.scheduling.partials.header', [
                        'page' => 'Subject Offered',
                        'title' => 'Subject Offered',
                        'description' => 'The subjects offered to the sections of a campus in a term.',
                    ])

                    @include('pages.employee.scheduling.partials.search-form', ['formId' => 'schedOfferingsSearchForm'])

                    <div class="card card-animate">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                            <h6 class="card-title mb-0"><i class="ti ti-server"></i> List of Subject Offered <small class="text-muted js-term-shown"></small></h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-success btn-sm text-nowrap d-none js-template" data-bs-toggle="modal" data-bs-target="#templateModal">
                                    <i class="fas fa-file-import"></i> Add from Curriculum
                                </button>
                                <button type="button" class="btn btn-success btn-sm text-light text-nowrap js-add">
                                    <i class="fas fa-plus"></i> Add Subject
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="schedOfferingsTable" class="table table-hover" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Section</th>
                                            <th>Subject</th>
                                            <th>Lec</th>
                                            <th>Lab</th>
                                            <th>Units</th>
                                            <th>Max</th>
                                            <th>Lec Fee</th>
                                            <th>Lab Fee</th>
                                            <th>Dev Fee</th>
                                            <th>Type</th>
                                            <th>Fund</th>
                                            <th>IT</th>
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

        @foreach (['add' => ['Add Subject Offered', 'store-url', 'POST'], 'edit' => ['Edit Subject Offered', 'update-url', 'PUT']] as $mode => [$title, $url, $method])
            <x-employee.form-modal :id="$mode . 'OfferingModal'" :title="$title" :form-id="$mode . 'OfferingForm'" :url="$url" :method="$method" size="xl">
                {{-- Add: the searched campus and term (offerings.js); the server checks them --}}
                <input type="hidden" name="campus">
                <input type="hidden" name="schlyear">
                <input type="hidden" name="semester">
                <div class="row g-3">
                    <div class="col-md-12">
                        <small class="text-muted js-modal-term"></small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Year & Section: <span class="text-danger">*</span></label>
                        {{-- The term's sections (filled by offerings.js from the list response) --}}
                        <select class="form-control form-control-sm js-sections" name="subSec" required></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Max Students: <span class="text-danger">*</span></label>
                        <input type="number" name="maxstud" class="form-control form-control-sm" min="1" max="500" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Type: <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" name="isType" required>
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    @include('pages.employee.scheduling.partials.subject-fields', ['funds' => $funds])
                </div>
            </x-employee.form-modal>
        @endforeach

        {{-- Add from Curriculum: a section's curriculum subjects (year level + semester), then save the new ones --}}
        <div class="modal fade" id="templateModal" tabindex="-1" aria-labelledby="templateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="templateModalLabel">Add from Curriculum <small class="text-muted js-modal-term"></small></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Year & Section: <span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm js-template-section"></select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-success btn-sm w-100 js-template-show"><i class="ti ti-list-search"></i> Show Curriculum Subjects</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Subject</th>
                                        <th>Units</th>
                                        <th>Fees (Lec / Lab / Dev)</th>
                                        <th>Fund</th>
                                        <th>OJT</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody class="js-template-rows">
                                    <tr><td colspan="8" class="text-center text-muted">Pick a section, then click Show Curriculum Subjects.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <small class="text-muted me-auto js-template-note"></small>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success js-template-save" disabled>Save as Subject Offered</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/scheduling/common.js" />
    <x-script src="js/employee/scheduling/offerings.js" />
@endpush
