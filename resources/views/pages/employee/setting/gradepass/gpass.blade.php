@extends('layouts.master_empsetting')

@section('title')
    CISS - Setting | Grades Pass
@endsection

@section('workspace')
    {{-- URLs for grade-passwords.js (__ID__ is replaced with the record's id) --}}
    <div id="grade-passwords"
        data-list-url="{{ route('employee.settings.grade-passwords.list') }}"
        data-store-url="{{ route('employee.settings.grade-passwords.store') }}"
        data-update-url="{{ route('employee.settings.grade-passwords.update', '__ID__') }}">

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
                                <li class="breadcrumb-item active mt-1">Grades Password</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Grades Password</h1>
                            <p class="text-muted small mb-0">Manage the grades password of each campus.</p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="card card-animate">
                                <div class="card-header d-flex justify-content-between align-items-center py-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-server"></i> List of Grades Password
                                    </h6>
                                    <button type="button" class="btn btn-success btn-sm text-light" data-bs-toggle="modal" data-bs-target="#addGradePassModal">
                                        <i class="ti ti-plus"></i> Add New
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="table-responsive p-2">
                                            <table id="setgradepasstable" class="table table-hover" style="width: 100%">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Grades Password</th>
                                                        <th>Campus</th>
                                                        <th>Date Created</th>
                                                        <th>Date Updated</th>
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

        <x-employee.form-modal id="addGradePassModal" title="Add Grades Password" form-id="addGradePassForm" url="store-url">
            <div class="form-group">
                <label>Campus: <span class="text-danger">*</span></label>
                <select name="campus" class="form-control form-control-sm" required>
                    <option value="" disabled selected>Select</option>
                    @foreach ($campuses as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mt-3">
                <label>Grades Password: <span class="text-danger">*</span></label>
                <input type="password" name="gradeauthpass" class="form-control form-control-sm" minlength="4" autocomplete="new-password" required>
            </div>
        </x-employee.form-modal>

        <x-employee.form-modal id="editGradePassModal" title="Edit Grades Password" form-id="editGradePassForm" url="update-url" method="PUT">
            <div class="form-group">
                <label>Campus:</label>
                <input type="text" class="form-control form-control-sm js-campus-name" readonly>
            </div>
            <div class="form-group mt-3">
                <label>New Grades Password: <span class="text-danger">*</span></label>
                <input type="password" name="gradeauthpass" class="form-control form-control-sm" minlength="4" autocomplete="new-password" required>
            </div>
        </x-employee.form-modal>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/settings/grade-passwords.js" />
@endpush
