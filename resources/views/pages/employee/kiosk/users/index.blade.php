@extends('layouts.master_empkiosk')

@section('title')
    CISS - Kiosk | Kiosk User
@endsection

@section('workspace')
    {{-- URLs for kiosk/users.js (__ID__ is replaced with the account's id, or the Student ID for student-url) --}}
    <div id="kioskUsers"
        data-list-url="{{ route('employee.kiosk.users.list') }}"
        data-store-url="{{ route('employee.kiosk.users.store') }}"
        data-reset-url="{{ route('employee.kiosk.users.reset', '__ID__') }}"
        data-destroy-url="{{ route('employee.kiosk.users.destroy', '__ID__') }}"
        data-student-url="{{ route('employee.kiosk.users.student', '__ID__') }}">

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
                                <li class="breadcrumb-item mt-1">Kiosk</li>
                                <li class="breadcrumb-item active mt-1">Kiosk User</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Student Kiosk User Account</h1>
                            <p class="text-muted small mb-0">Students' kiosk accounts: create one, reset a forgotten password or delete it.</p>
                        </div>
                    </div>
                    <div class="card card-animate mb-3">
                        <div class="card-header pt-3">
                            <h6 class="card-title">
                                <i class="ti ti-search"></i> Search to show data
                            </h6>
                        </div>
                        <div class="card-body">
                            {{-- Searching reloads the table below (no page reload); nothing is listed before a search --}}
                            <form id="kioskUsersSearchForm" autocomplete="off">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Campus: <span class="text-danger">*</span></label>
                                        {{-- Administrators: every campus; everyone else: their own --}}
                                        <select class="form-control form-control-sm" name="campus" required>
                                            @foreach ($campuses as $code => $name)
                                                <option value="{{ $code }}" @selected($code === $userCampus)>{{ str_replace(' Campus', '', $name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Last Name, First Name or Student ID No.: <span class="text-danger">*</span></label>
                                        <input type="text" name="query" class="form-control form-control-sm text-uppercase" placeholder="Search Last Name, First Name or Student ID No." minlength="2" maxlength="50" required autofocus>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="d-flex flex-column h-100">
                                            <label class="form-label fw-semibold opacity-0 d-none d-md-block">Action</label>
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="ti ti-search"></i> Search
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card card-animate">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                            <h6 class="card-title mb-0">
                                <i class="ti ti-users"></i> List of registered students in Kiosk
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <small class="text-muted js-limit-note"></small>
                                <button type="button" class="btn btn-success btn-sm text-light text-nowrap" data-bs-toggle="modal" data-bs-target="#addKioskUserModal">
                                    <i class="fas fa-user-plus"></i> Add New
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-2">
                                <table id="kioskUsersTable" class="table table-hover" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Student ID No.</th>
                                            <th>Name</th>
                                            <th>No. of Reset</th>
                                            <th>Last Changed</th>
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

        <x-employee.form-modal id="addKioskUserModal" title="Add Kiosk Account" form-id="addKioskUserForm" url="store-url">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Student ID No.: <span class="text-danger">*</span></label>
                    {{-- Typed as 2024-0001-K (crud.js formats it); the name is looked up as it is typed (kiosk/users.js) --}}
                    <input type="text" name="studid" class="form-control js-student-id js-kiosk-student-id" placeholder="0000-0000-X" maxlength="11" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Name:</label>
                    <input type="text" class="form-control js-student-name" readonly tabindex="-1">
                    <small class="js-student-note"></small>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Password: <span class="text-danger">*</span></label>
                    {{-- The Generate button sits inside the field, on the right (.kiosk-password-field) --}}
                    <div class="kiosk-password-field">
                        <input type="text" name="password" class="form-control text-uppercase js-password" minlength="4" maxlength="20" autocomplete="off" required>
                        <button type="button" class="btn btn-sm btn-warning js-generate-password"><i class="fas fa-key"></i> Generate</button>
                    </div>
                    <small class="text-muted">Give this password to the student now: only its protected form is saved.</small>
                </div>
            </div>
        </x-employee.form-modal>

        <x-employee.form-modal id="resetKioskUserModal" title="Reset Kiosk Password" form-id="resetKioskUserForm" url="reset-url" method="PUT">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Student:</label>
                    <input type="text" class="form-control js-account-student" readonly tabindex="-1">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">New Password: <span class="text-danger">*</span></label>
                    <div class="kiosk-password-field">
                        <input type="text" name="password" class="form-control text-uppercase js-password" minlength="4" maxlength="20" autocomplete="off" required>
                        <button type="button" class="btn btn-sm btn-warning js-generate-password"><i class="fas fa-key"></i> Generate</button>
                    </div>
                    <small class="text-muted">Give this password to the student now: only its protected form is saved.</small>
                </div>
            </div>
        </x-employee.form-modal>
    </div>
@endsection

@push('styles')
    <style>
        /* Password field with its Generate button inside, on the right; the text stops before the button */
        .kiosk-password-field {
            position: relative;
        }

        .kiosk-password-field .form-control {
            padding-right: 7.5rem;
        }

        .kiosk-password-field .js-generate-password {
            position: absolute;
            top: 50%;
            right: 0.35rem;
            transform: translateY(-50%);
            z-index: 5;
        }
    </style>
@endpush

@push('scripts')
    <x-script src="js/employee/kiosk/users.js" />
@endpush
