@extends('layouts.master_empnstp')

@section('title')
    CISS - NSTP | Dashboard
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
                            <li class="breadcrumb-item mt-1">NSTP</li>
                            <li class="breadcrumb-item active mt-1">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">NSTP Dashboard Overview</h1>
                        <p class="text-muted small mb-0">
                            @if ($term)
                                Students taking NSTP subjects in <strong>{{ $term->label }}</strong>: <strong>{{ $total }}</strong>.
                            @else
                                No active school year and semester is set (Accounts &gt; A.Y. &amp; Semester).
                            @endif
                        </p>
                    </div>
                </div>

                {{-- One card per component; opens its student list --}}
                <div class="row g-3 mb-3">
                    @foreach ($counts as $count)
                        <div class="col-lg-3 col-md-6 col-12">
                            <a href="{{ route('employee.nstp.students.index', $count['key']) }}" wire:navigate class="text-reset text-decoration-none">
                                <div class="card card-animate h-100">
                                    <div class="card-body p-6">
                                        <div class="d-flex justify-content-between pb-2">
                                            <div>
                                                <h3 class="fw-bold h1">{{ $count['total'] }}</h3>
                                                <span>{{ $count['component'] }}</span>
                                                <div class="small text-muted">{{ $count['name'] }}</div>
                                            </div>
                                            <div>
                                                <i class="ti ti-users fs-1 text-success"></i>
                                            </div>
                                        </div>
                                        <small class="text-muted">Male: {{ $count['male'] }} &middot; Female: {{ $count['female'] }}</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
