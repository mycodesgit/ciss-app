@extends('layouts.master_empadmission')

@section('title')
    CISS - Admission | Availability Slots
@endsection

@section('workspace')
    {{-- URL for slots.js --}}
    <div id="slots" data-list-url="{{ route('employee.admission.slots.list') }}">

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
                                <li class="breadcrumb-item mt-1">Admission</li>
                                <li class="breadcrumb-item active mt-1">Availability Slots</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">Availability Slots</h1>
                            <p class="text-muted small mb-0">Examination schedules per date with booked and remaining slots.</p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            {{-- Changing Year / Campus reloads the slots (no page reload), see slots.js --}}
                            <div class="card card-animate">
                                <div class="card-header pt-3">
                                    <h6 class="card-title">
                                        <i class="ti ti-search"></i> Search to show data
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="slotsFilterForm">
                                        <div class="row g-3">
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Year: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="year">
                                                    @foreach ($years as $year)
                                                        <option value="{{ $year }}" @selected($year == $currentYear)>{{ $year }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Campus: <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" name="campus">
                                                    @foreach ($campuses as $code => $name)
                                                        <option value="{{ $code }}" @selected($code === $userCampus)>{{ str_replace(' Campus', '', $name) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card border-0 card-animate">
                                <div id="slot-container" class="row g-3">
                                    <!-- AJAX content (partials/slot-cards.blade.php) -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        @keyframes pulse-blink {
            0% { opacity: 1; }
            50% { opacity: 0.2; }
            100% { opacity: 1; }
        }

        .blink-dot { animation: pulse-blink 1.2s infinite ease-in-out; font-size: 0.55rem; vertical-align: middle; }
        #slot-container.is-loading { opacity: 0.5; pointer-events: none; transition: opacity 0.15s; }
    </style>
@endpush

@push('scripts')
    <x-script src="js/employee/admission/slots.js" />
@endpush
