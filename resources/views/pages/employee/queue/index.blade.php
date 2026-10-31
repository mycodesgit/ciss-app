@extends('layouts.master_empqueue')

@section('title')
    CISS - Queueing | Dashboard
@endsection

@section('workspace')
    {{-- URL for queue/dashboard.js (polled every few seconds) --}}
    <div id="queueDashboard" data-url="{{ route('employee.queue.dashboard.data', ['campus' => $campus]) }}">

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
                                <li class="breadcrumb-item active mt-1">Dashboard</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Queueing Dashboard Overview</h1>
                            <p class="text-muted small mb-0">
                                Live counters, waiting numbers and today's activity.
                                <span class="badge js-queue-mode"></span>
                                <span class="text-muted ms-1">Updated <span class="js-updated">-</span></span>
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @include('pages.employee.queue.partials.campus-select')
                            <a href="{{ route('queue-monitor.show', $campus) }}" target="_blank" rel="noopener" class="btn btn-outline-success btn-sm text-nowrap">
                                <i class="fas fa-tv"></i> Open Queue Monitor
                            </a>
                        </div>
                    </div>

                    {{-- Totals --}}
                    <div class="row g-3 mb-3">
                        @foreach ([['waiting', 'Waiting', 'fas fa-clock text-primary'], ['serving', 'Being Served', 'fas fa-user-check text-success'], ['handledToday', 'Handled Today', 'fas fa-check-circle text-secondary'], ['open', 'Open Counters', 'fas fa-desktop text-info']] as [$key, $label, $icon])
                            <div class="col-lg-3 col-6">
                                <div class="card card-animate h-100">
                                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="fw-bold h2 mb-0 js-total-{{ $key }}">-</h3>
                                            <small class="text-muted">{{ $label }}</small>
                                        </div>
                                        <i class="{{ $icon }} fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row g-3 mb-3">
                        {{-- One card per counter --}}
                        <div class="col-lg-9">
                            <div class="card card-animate h-100">
                                <div class="card-header pt-3">
                                    <h6 class="card-title mb-0"><i class="fas fa-tv text-success me-1"></i> Counters</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 js-counters">
                                        <div class="col-12 text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span> Loading...</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Next waiting number per category --}}
                        <div class="col-lg-3">
                            <div class="card card-animate h-100">
                                <div class="card-header pt-3">
                                    <h6 class="card-title mb-0"><i class="fas fa-list-ol text-primary me-1"></i> Next In Line</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush js-next-in-line"></ul>
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
    <x-script src="js/employee/queue/dashboard.js" />
@endpush
