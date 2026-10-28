@extends('layouts.master_empsetting')

@section('title')
    CISS - Setting | Campuses Control
@endsection

@section('workspace')
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
                            <li class="breadcrumb-item active mt-1">Campuses Control</li>
                        </ol>
                    </div>
                </div>
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Campuses Control</h1>
                        <p class="text-muted small mb-0">Allow or block the Student Login of each campus. Changes are saved immediately.</p>
                    </div>
                </div>

                <div class="row g-3">
                    @forelse ($campuses as $campus)
                        <div class="col-md-6 col-xl-3">
                            <div class="card card-animate h-100">
                                <div class="card-header d-flex justify-content-between align-items-center py-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-building"></i> {{ $campus->name }}
                                    </h6>
                                    <span @class(['badge js-control-badge', 'bg-success' => $campus->login_enabled, 'bg-danger' => !$campus->login_enabled])
                                        data-on="Allowed" data-off="Blocked">
                                        {{ $campus->login_enabled ? 'Allowed' : 'Blocked' }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="icheck-success">
                                            <input type="checkbox" id="campus-login-{{ $campus->id }}" class="js-control-toggle"
                                                data-url="{{ route('employee.settings.campuses.update', $campus) }}"
                                                data-label="{{ $campus->name }}"
                                                @checked($campus->login_enabled)>
                                            <label for="campus-login-{{ $campus->id }}">
                                                <h5 class="mb-0" style="margin-top: -3px">Student Login</h5>
                                            </label>
                                        </div>
                                        <i class="ti ti-login fs-1 text-info"></i>
                                    </div>
                                    <h6 class="mt-3"><i class="icon fas fa-exclamation-circle text-warning"></i> Note!</h6>
                                    <span class="small">Check to <strong>"Allow"</strong> students of this campus to log in.</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-muted">No campuses found.</div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/employee/settings/control-status.js') }}"></script>
@endpush
