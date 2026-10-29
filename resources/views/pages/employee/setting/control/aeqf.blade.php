@extends('layouts.master_empsetting')

@section('title')
    CISS - Setting | Control Status
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
                            <li class="breadcrumb-item active mt-1">Control Status</li>
                        </ol>
                    </div>
                </div>
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Control Status</h1>
                        <p class="text-muted small mb-0">Turn the Admission Portal, Enrollment, Queueing and Faculty Evaluation on or off. Changes are saved immediately.</p>
                    </div>
                </div>

                <div class="row g-3">
                    @foreach ($controls as $control)
                        <div class="col-md-6 col-xl-4">
                            <div class="card card-animate h-100">
                                <div class="card-header d-flex justify-content-between align-items-center py-3">
                                    <h6 class="card-title mb-0">
                                        <i class="{{ $control['icon'] }}"></i> {{ $control['title'] }}
                                    </h6>
                                    <span @class(['badge js-control-badge', 'bg-success' => $control['is_on'], 'bg-danger' => !$control['is_on']])>
                                        {{ $control['is_on'] ? 'On' : 'Off' }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="icheck-success">
                                        <input type="checkbox" id="control-{{ $control['key'] }}" class="js-control-toggle"
                                            data-url="{{ route('employee.settings.control-status.update', $control['key']) }}"
                                            data-label="{{ $control['label'] }}"
                                            @checked($control['is_on'])>
                                        <label for="control-{{ $control['key'] }}">
                                            <h5 class="mb-0" style="margin-top: -3px">{{ $control['label'] }}</h5>
                                        </label>
                                    </div>
                                    <h6 class="mt-3"><i class="icon fas fa-exclamation-triangle text-warning"></i> Note!</h6>
                                    <span class="small">{!! $control['note'] !!}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/settings/control-status.js" />
@endpush
