@extends('layouts.master_empkiosk')

@section('title')
    CISS - Kiosk | Dashboard
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
                            <li class="breadcrumb-item mt-1">Kiosk</li>
                            <li class="breadcrumb-item active mt-1">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Student Kiosk Dashboard Overview</h1>
                        <p class="text-muted small mb-0">Your kiosk logs: the student kiosk accounts you created or reset in Kiosk User.</p>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-9">
                        <div class="card card-animate h-100">
                            <div class="card-header pt-3">
                                <h6 class="card-title mb-0">Monthly Kiosk Logs for {{ now()->year }}</h6>
                            </div>
                            <div class="card-body">
                                {{-- Drawn by kiosk/dashboard.js --}}
                                <div style="height: 250px;">
                                    <canvas id="kioskLogsChart" data-monthly='@json($monthly)'></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-animate mb-3">
                            <div class="card-body p-6">
                                <div class="d-flex justify-content-between pb-2">
                                    <div>
                                        <h3 class="fw-bold h1">{{ $today }}</h3>
                                        <span>Today's Logs</span>
                                    </div>
                                    <div>
                                        <i class="ti ti-users fs-1 text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card card-animate">
                            <div class="card-body p-6">
                                <div class="d-flex justify-content-between pb-2">
                                    <div>
                                        <h3 class="fw-bold h1">{{ $yesterday }}</h3>
                                        <span>Yesterday's Logs</span>
                                    </div>
                                    <div>
                                        <i class="ti ti-users fs-1 text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title mb-0"><i class="ti ti-calendar-stats"></i> Students You Catered per Month</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Number of Students You Catered</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($perMonth as $row)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::create($row->year, $row->month)->format('F Y') }}</td>
                                                    <td>{{ $row->total }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" class="text-center text-muted">No kiosk logs yet.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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
    <x-script src="js/employee/kiosk/dashboard.js" />
@endpush
