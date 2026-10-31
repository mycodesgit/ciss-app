@extends('layouts.master_empscheduling')

@section('title')
    CISS - Scheduling | Curriculum Programs
@endsection

@section('workspace')
    {{-- URLs and options for scheduling/curricula.js (__ID__ = curriculum id, __CODE__ = subject code) --}}
    <div id="schedCurricula"
        data-list-url="{{ route('employee.scheduling.curricula.list') }}"
        data-store-url="{{ route('employee.scheduling.curricula.store') }}"
        data-destroy-url="{{ route('employee.scheduling.curricula.destroy', '__ID__') }}"
        data-pdf-url="{{ route('employee.scheduling.curricula.pdf') }}"
        data-index-url="{{ route('employee.scheduling.curricula.index') }}"
        data-subjects-url="{{ route('employee.scheduling.lookup.subjects') }}"
        data-fees-url="{{ route('employee.scheduling.lookup.fees', '__CODE__') }}"
        data-ojt-options='@json($ojtOptions)'>

        <div class="row">
            <div class="col-12">
                <div class="mb-6">
                    @include('pages.employee.scheduling.partials.header', [
                        'page' => 'Curriculum Programs',
                        'title' => 'Curriculum Programs',
                        'description' => 'The subjects of a program per year level and semester.',
                    ])

                    <div class="card card-animate mb-3">
                        <div class="card-header pt-3">
                            <h6 class="card-title"><i class="ti ti-search"></i> Search to show data</h6>
                        </div>
                        <div class="card-body">
                            <form id="schedCurriculaSearchForm" autocomplete="off">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Campus: <span class="text-danger">*</span></label>
                                        {{-- Another campus reloads the page with that campus's programs --}}
                                        <select class="form-control form-control-sm js-campus" name="campus" required>
                                            @foreach ($campuses as $code => $name)
                                                <option value="{{ $code }}" @selected($code === request('campus', $userCampus))>{{ str_replace(' Campus', '', $name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Program: <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm" name="progCod" required>
                                            <option value="" selected disabled>Select a program</option>
                                            @foreach ($programs as $program)
                                                <option value="{{ $program->progCod }}">{{ $program->progAcronym }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Semester: <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm" name="semester" required>
                                            @foreach ($semesters as $value => $label)
                                                <option value="{{ $value }}" @selected($value === $activeSemester)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="d-flex flex-column h-100">
                                            <label class="form-label fw-semibold opacity-0 d-none d-md-block">Action</label>
                                            <button type="submit" class="btn btn-success btn-sm"><i class="ti ti-search"></i> Search</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card card-animate">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                            <h6 class="card-title mb-0"><i class="ti ti-server"></i> Subjects <small class="text-muted js-term-shown"></small></h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-danger btn-sm text-nowrap js-view-pdf" disabled>
                                    <i class="fas fa-file-pdf"></i> Whole Curriculum
                                </button>
                                <button type="button" class="btn btn-success btn-sm text-light text-nowrap js-add" data-bs-toggle="modal" data-bs-target="#addCurriculumModal" disabled>
                                    <i class="fas fa-plus"></i> Add Subject
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="schedCurriculaTable" class="table table-hover" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Year</th>
                                            <th>Code</th>
                                            <th>Subject</th>
                                            <th>Lec</th>
                                            <th>Lab</th>
                                            <th>Units</th>
                                            <th>Fees (Lec / Lab / Dev)</th>
                                            <th>Fund</th>
                                            <th>IT Subj</th>
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

        <x-employee.form-modal id="addCurriculumModal" title="Add Subject to Curriculum" form-id="addCurriculumForm" url="store-url" size="xl">
            {{-- The searched campus, program and semester (curricula.js); the server checks them --}}
            <input type="hidden" name="campus">
            <input type="hidden" name="progCod">
            <input type="hidden" name="semester">
            <div class="row g-3">
                <div class="col-md-12">
                    <small class="text-muted js-modal-term"></small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Year Level: <span class="text-danger">*</span></label>
                    <select class="form-control form-control-sm" name="yrlvl" required>
                        <option value="" selected disabled>--Select--</option>
                        @foreach ($yearLevels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Pre-Requisite:</label>
                    <select class="form-control form-control-sm js-prerequisite" name="prerequisite"></select>
                </div>

                @include('pages.employee.scheduling.partials.subject-fields', ['funds' => $funds])
            </div>
        </x-employee.form-modal>

        <x-employee.pdf-modal id="curriculumPdfModal" title="Curriculum" />
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/scheduling/common.js" />
    <x-script src="js/employee/scheduling/curricula.js" />
@endpush
