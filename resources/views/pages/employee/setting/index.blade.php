@extends('layouts.master_empsetting')

@section('title')
    CISS - Setting | Dashboard
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
                            <li class="breadcrumb-item active mt-1">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">System Dashboard Overview</h1>
                        <p class="text-muted small mb-0">System metrics, configuration, and daily activity logs.</p>
                    </div>
                </div>

                @if ($overview)
                    @php
                        $cards = [
                            ['label' => 'Total Users', 'value' => number_format($overview['userCounts']), 'icon' => 'ti ti-users', 'color' => 'info', 'tagIcon' => 'ti ti-users', 'tag' => 'System', 'note' => 'total registered users'],
                            ['label' => 'Total Active Users', 'value' => number_format($overview['userActiveCounts']), 'icon' => 'ti ti-users', 'color' => 'success', 'tagIcon' => 'ti ti-check', 'tag' => 'Active', 'note' => 'currently active accounts'],
                            ['label' => 'Total Unactive Users', 'value' => number_format($overview['userInactiveCounts']), 'icon' => 'ti ti-users', 'color' => 'danger', 'tagIcon' => 'ti ti-x', 'tag' => 'Inactive', 'note' => 'disabled or idle accounts'],
                            ['label' => "Added User's Today", 'value' => number_format($overview['userAddedTodayCounts']), 'icon' => 'ti ti-user-plus', 'color' => 'success', 'tagIcon' => 'ti ti-arrow-up-right', 'tag' => 'Today', 'note' => 'new registrations'],
                            ['label' => 'Admission Status', 'value' => $overview['admissionStatus'] ?? 'N/A', 'icon' => 'ti ti-calendar-user', 'color' => 'success', 'tagIcon' => 'ti ti-info-circle', 'tag' => 'Status', 'note' => 'current admission mode'],
                            ['label' => 'Enrollment Status', 'value' => $overview['enrollmentStatus'] ?? 'N/A', 'icon' => 'ti ti-device-laptop', 'color' => 'success', 'tagIcon' => 'ti ti-info-circle', 'tag' => 'Status', 'note' => 'current enrollment mode'],
                            ['label' => 'Queueing Status', 'value' => $overview['queueStatus'] ?? 'N/A', 'icon' => 'ti ti-line', 'color' => 'success', 'tagIcon' => 'ti ti-info-circle', 'tag' => 'Status', 'note' => 'queue system operational state'],
                            ['label' => 'Faculty Eval. Status', 'value' => 'N/A', 'icon' => 'ti ti-file-analytics', 'color' => 'success', 'tagIcon' => 'ti ti-info-circle', 'tag' => 'Status', 'note' => 'faculty evaluation system state'],
                            ['label' => 'Server Maintenance Status', 'value' => 'Off', 'icon' => 'ti ti-server', 'color' => 'success', 'tagIcon' => 'ti ti-check', 'tag' => 'Normal', 'note' => 'server status operating'],
                        ];
                    @endphp

                    <div class="row g-3 mb-3">
                        @foreach ($cards as $card)
                            <div class="col-xl-3 col-sm-6">
                                <div class="card card-animate p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small fw-medium">{{ $card['label'] }}</span>
                                        <i class="{{ $card['icon'] }} text-{{ $card['color'] }} fs-5"></i>
                                    </div>
                                    <div class="h2 fw-bold mb-1">{{ $card['value'] }}</div>
                                    <small class="text-muted"><span class="text-{{ $card['color'] }} fw-semibold"><i class="{{ $card['tagIcon'] }}"></i> {{ $card['tag'] }}</span> {{ $card['note'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
