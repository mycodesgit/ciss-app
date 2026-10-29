@extends('layouts.master_empenrollment')

@section('title')
    CISS - Enrollment | Dashboard
@endsection

@php
    // Stat cards: [count key, label, PDF list group (clickable) or null]
    $levelCards = [
        ['year1', '1st Year Students Enrolled this Sem', null],
        ['year2', '2nd Year Students Enrolled this Sem', null],
        ['year3', '3rd Year Students Enrolled this Sem', null],
        ['year4', '4th Year Students Enrolled this Sem', null],
        ['transferee', 'Transferee Students Enrolled this Sem', 'transferee'],
        ['shiftee', 'Shiftee Students Enrolled this Sem', null],
        ['continuing', 'Continuing Students Enrolled this Sem', null],
        ['returning', 'Returnee Students Enrolled this Sem', 'returnee'],
    ];
    $statusCards = [
        ['regular', 'Regular Students Enrolled this Sem', 'regular'],
        ['irregular', 'Irregular Students Enrolled this Sem', 'irregular'],
    ];
    $groups = \App\Http\Controllers\Employee\Enrollment\EnrollmentDashboardController::STUDENT_GROUPS;
@endphp

@section('workspace')
    {{-- URLs for enrollment/dashboard.js (__GROUP__ is replaced with regular / irregular / transferee / returnee) --}}
    <div id="enrollmentDashboard"
        data-charts-url="{{ route('employee.enrollment.dashboard.charts') }}"
        data-pdf-url="{{ route('employee.enrollment.dashboard.students-pdf', '__GROUP__') }}">

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
                                <li class="breadcrumb-item mt-1">Enrollment</li>
                                <li class="breadcrumb-item active mt-1">Dashboard</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Enrollment Dashboard Overview</h1>
                            <p class="text-muted small mb-0">
                                @if ($term)
                                    Enrolled students for <strong>{{ $term->label }}</strong>.
                                @else
                                    Enrollment summary of the active school year.
                                @endif
                            </p>
                        </div>
                    </div>

                    @if (! $term)
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle"></i> No active school year found. Set one in Accounts &gt; A.Y. &amp; Semester.
                        </div>
                    @else
                        <div class="row g-3">
                            @unless ($isGraduate)
                                {{-- Year level / type cards (click a card with a list icon to view its students as PDF) --}}
                                @foreach ($levelCards as [$key, $label, $group])
                                    <div class="col-lg-3 col-12">
                                        @include('pages.employee.enrollment.partials.stat-card', ['count' => $stats[$key] ?? 0])
                                    </div>
                                @endforeach

                                <div class="col-md-6">
                                    <div class="card card-animate">
                                        <div class="card-body">
                                            <div class="chart-box"><canvas id="previousCollegeChart"></canvas></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-animate">
                                        <div class="card-body">
                                            <div class="chart-box"><canvas id="currentCollegeChart"></canvas></div>
                                        </div>
                                    </div>
                                </div>
                            @endunless

                            <div class="col-md-6">
                                <div class="card card-animate">
                                    <div class="card-body">
                                        <div class="chart-box"><canvas id="previousLevelChart"></canvas></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-animate">
                                    <div class="card-body">
                                        <div class="chart-box"><canvas id="currentLevelChart"></canvas></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="card card-animate">
                                    <div class="card-body">
                                        <div class="chart-box"><canvas id="programChart"></canvas></div>
                                    </div>
                                </div>
                            </div>

                            @unless ($isGraduate)
                                <div class="col-md-12">
                                    <div class="card card-animate">
                                        <div class="card-header pt-3">
                                            <h6 class="card-title">Enrollment for this current Semester in All Campuses</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-box"><canvas id="campusChart"></canvas></div>
                                        </div>
                                    </div>
                                </div>

                                @foreach ($statusCards as [$key, $label, $group])
                                    <div class="col-lg-6 col-12">
                                        @include('pages.employee.enrollment.partials.stat-card', ['count' => $stats[$key] ?? 0])
                                    </div>
                                @endforeach
                            @endunless
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <x-employee.pdf-modal id="studentsPdfModal" title="Students List" />
    </div>
@endsection

@push('styles')
    <style>
        #enrollmentDashboard .chart-box { position: relative; height: 330px; }
        #enrollmentDashboard .js-student-list { cursor: pointer; }
    </style>
@endpush

@push('scripts')
    <x-script src="js/employee/enrollment/dashboard.js" />
@endpush
