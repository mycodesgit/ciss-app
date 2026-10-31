@extends('layouts.master_empqueue')

@section('title')
    CISS - Queueing | Settings
@endsection

@section('workspace')
    {{-- URLs for queue/settings.js --}}
    <div id="queueSettings"
        data-toggle-url="{{ route('employee.queue.settings.toggle') }}"
        data-reset-url="{{ route('employee.queue.settings.reset', ['campus' => $campus]) }}"
        data-campus-name="{{ str_replace(' Campus', '', $campuses[$campus] ?? $campus) }}">

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
                                <li class="breadcrumb-item mt-1">Queueing</li>
                                <li class="breadcrumb-item active mt-1">Settings</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Settings</h1>
                            <p class="text-muted small mb-0">Turn the queueing on or off, and start a campus's queue over.</p>
                        </div>
                        @include('pages.employee.queue.partials.campus-select')
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card card-animate h-100">
                                <div class="card-header pt-3">
                                    <h6 class="card-title mb-0"><i class="ti ti-settings"></i> Queueing Mode</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch fs-5">
                                        <input class="form-check-input js-queue-toggle" type="checkbox" role="switch" id="queueToggle" @checked($queueOn)>
                                        <label class="form-check-label" for="queueToggle">Queueing is <strong class="js-queue-state">{{ $queueOn ? 'On' : 'Off' }}</strong></label>
                                    </div>
                                    <p class="small text-muted mt-3 mb-0">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                        One switch for every campus (the same as Accounts &gt; Control Status). When it is Off, the queue panel of Enroll Student is hidden.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-animate h-100">
                                <div class="card-header pt-3">
                                    <h6 class="card-title mb-0"><i class="fas fa-rotate"></i> Reset Queueing</h6>
                                </div>
                                <div class="card-body">
                                    <button type="button" class="btn btn-outline-danger js-queue-reset">
                                        <i class="fas fa-rotate"></i> Reset the queue of {{ str_replace(' Campus', '', $campuses[$campus] ?? $campus) }}
                                    </button>
                                    <p class="small text-muted mt-3 mb-0">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                        Every number of this campus goes back to waiting and every window serves nothing, as at the start of the day. This cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <x-script src="js/employee/queue/settings.js" />
@endpush
