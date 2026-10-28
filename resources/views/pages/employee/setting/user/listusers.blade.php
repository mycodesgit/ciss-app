@extends('layouts.master_empsetting')

@section('title')
    CISS V.1.0 || Settings
@endsection

@section('workspace')
    {{-- URLs for users.js (__ID__ is replaced with the user's id) --}}
    <div id="user-management"
        data-list-url="{{ route('employee.settings.users.list') }}"
        data-store-url="{{ route('employee.settings.users.store') }}"
        data-update-url="{{ route('employee.settings.users.update', '__ID__') }}"
        data-password-url="{{ route('employee.settings.users.password', '__ID__') }}"
        data-status-url="{{ route('employee.settings.users.status', '__ID__') }}"
        data-menu-access-url="{{ route('employee.settings.users.menu-access.update', '__ID__') }}"
        data-year-access-url="{{ route('employee.settings.users.year-access.update', '__ID__') }}">

        <div class="row">
            <div class="col-12">
                <div class="mb-6">
                    <div class="card" style=" background-color: #e9ecef; margin-top: -10px">
                        <div class="card-body">
                            <ol class="breadcrumb" style="margin-bottom: -3px;">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('employee.dashboard') }}" wire:navigate class="btn btn-success btn-sm text-light">
                                        <i class="fas fa-home"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item mt-1">Settings</li>
                                <li class="breadcrumb-item active mt-1">User's Management</li>
                            </ol>
                        </div>
                    </div>
                    <div class="row g-3 mb-3 mt-3">
                        <div class="col-md-12">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="page-header" style="border-bottom: 1px solid #04401f;">
                                        <h4>User's Management</h4>
                                    </div>
                                    <div class="row">
                                        <div class="table-responsive p-3 mt-3">
                                            <button type="button" class="btn btn-success btn-sm mb-4 text-light" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                                <i class="fas fa-user-plus"></i> Add New
                                            </button>
                                            <table id="userlist" class="table table-hover w-100">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Role</th>
                                                        <th>Campus</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
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

        <x-employee.form-modal id="addUserModal" title="Add New User" form-id="addUserForm" url="store-url" size="lg">
            @include('pages.employee.setting.user.partials.user-fields', ['withPassword' => true])
        </x-employee.form-modal>

        <x-employee.form-modal id="editUserModal" title="Edit User" form-id="editUserForm" url="update-url" method="PUT" size="lg">
            @include('pages.employee.setting.user.partials.user-fields')
        </x-employee.form-modal>

        <x-employee.form-modal id="userPasswordModal" title="Change User Password" form-id="userPasswordForm" url="password-url" method="PUT" size="sm">
            @include('pages.employee.setting.user.partials.user-summary', ['withRole' => false])
            <div class="form-group mt-3">
                <label>New Password: <span class="text-danger">*</span></label>
                <input type="password" name="password" placeholder="At least 8 characters" class="form-control form-control-sm" minlength="8" autocomplete="new-password" required>
            </div>
        </x-employee.form-modal>

        <x-employee.form-modal id="userMenuAccessModal" title="User Access" form-id="userMenuAccessForm" url="menu-access-url" method="PUT">
            @include('pages.employee.setting.user.partials.user-summary')
            <div class="form-group mt-3">
                <label>Select Buttons</label>
                @foreach ($menuButtons as $value => $label)
                    <div class="icheck-success">
                        <input type="checkbox" id="access-{{ $value }}" name="buttons[]" value="{{ $value }}">
                        <label for="access-{{ $value }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </x-employee.form-modal>

        <x-employee.form-modal id="userYearAccessModal" title="Allow User Access for Academic Year" form-id="userYearAccessForm" url="year-access-url" method="PUT">
            @include('pages.employee.setting.user.partials.user-summary')
            <div class="form-group mt-3">
                <label>Select Academic Year</label>
                @forelse ($schoolYears as $schoolYear)
                    <div class="icheck-success">
                        <input type="checkbox" id="year-{{ $schoolYear }}" name="schlyraccess[]" value="{{ $schoolYear }}">
                        <label for="year-{{ $schoolYear }}">{{ $schoolYear }}</label>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No academic years configured yet.</p>
                @endforelse
            </div>
        </x-employee.form-modal>

        <x-employee.form-modal id="userStatusModal" title="Change User Status" form-id="userStatusForm" url="status-url" method="PUT" size="sm">
            @include('pages.employee.setting.user.partials.user-summary', ['withRole' => false])
            <div class="form-group mt-3">
                <label>Change User Status: <span class="text-danger">*</span></label>
                <select name="statuser" class="form-control form-control-sm" required>
                    <option value="1">Enable</option>
                    <option value="2">Disabled</option>
                </select>
            </div>
        </x-employee.form-modal>
    </div>
@endsection

@push('styles')
    <style>
        .badge.bg-ossa {
            background-color: #e83e8c;
            color: #fff;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/employee/settings/users.js') }}"></script>
@endpush
