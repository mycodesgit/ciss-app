@extends('layouts.master_empsetting')

@section('title')
    CISS V.1.0 || Settings
@endsection

@section('workspace')
    {{-- URLs for academic-terms.js (__ID__ is replaced with the record's id) --}}
    <div id="academic-terms"
        data-list-url="{{ route('employee.settings.academic-terms.list') }}"
        data-store-url="{{ route('employee.settings.academic-terms.store') }}"
        data-update-url="{{ route('employee.settings.academic-terms.update', '__ID__') }}">

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
                                <li class="breadcrumb-item mt-1">Settings</li>
                                <li class="breadcrumb-item active mt-1">A.Y. & Semester</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">A.Y. & Semester</h1>
                            <p class="text-muted small mb-0">Manage academic year and semester.</p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="card card-animate">
                                <div class="card-header d-flex justify-content-between align-items-center py-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-server"></i> List of A.Y. & Semester
                                    </h6>
                                    <button type="button" class="btn btn-success btn-sm text-light" data-bs-toggle="modal" data-bs-target="#addTermModal">
                                        <i class="ti ti-plus"></i> Add New
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="table-responsive p-2">
                                            <table id="setconftable" class="table table-hover" style="width: 100%">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>School Year</th>
                                                        <th>Semester</th>
                                                        <th>Status</th>
                                                        <th width="10%">Action</th>
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

        <x-employee.form-modal id="addTermModal" title="Add A.Y. & Semester" form-id="addTermForm" url="store-url">
            @include('pages.employee.setting.aysem.partials.term-fields')
        </x-employee.form-modal>

        <x-employee.form-modal id="editTermModal" title="Edit A.Y. & Semester" form-id="editTermForm" url="update-url" method="PUT">
            @include('pages.employee.setting.aysem.partials.term-fields')
        </x-employee.form-modal>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/employee/settings/academic-terms.js') }}"></script>
@endpush
