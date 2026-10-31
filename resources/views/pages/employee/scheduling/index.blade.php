@extends('layouts.master_empscheduling')

@section('title')
    CISS - Scheduling | Dashboard
@endsection

@section('workspace')
    @php
        $cards = [
            ['value' => $counts['colleges'], 'label' => 'Colleges', 'icon' => 'ti ti-buildings'],
            ['value' => $counts['undergraduate'], 'label' => 'Undergraduate Programs', 'icon' => 'ti ti-book'],
            ['value' => $counts['graduate'], 'label' => 'Graduate Programs', 'icon' => 'ti ti-books'],
            ['value' => $counts['rooms'], 'label' => 'Rooms in Your Campus', 'icon' => 'ti ti-route-square'],
        ];
        // Month calendar: blank cells before the 1st, then the days
        $leadingBlanks = $month->dayOfWeek;
        $days = $month->daysInMonth;
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="mb-6">
                @include('pages.employee.scheduling.partials.header', [
                    'page' => 'Dashboard',
                    'title' => 'Scheduler Dashboard Overview',
                    'description' => 'Subject offering, and plotting of class, faculty and room schedules.',
                ])

                <div class="row g-3 mb-3">
                    @foreach ($cards as $card)
                        <div class="col-lg-3 col-12">
                            <div class="card card-animate">
                                <div class="card-body p-6">
                                    <div class="d-flex justify-content-between pb-2">
                                        <div>
                                            <h3 class="fw-bold h1">{{ number_format($card['value']) }}</h3>
                                            <span>{{ $card['label'] }}</span>
                                        </div>
                                        <div>
                                            <i class="{{ $card['icon'] }} fs-1 text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row g-3">
                    <div class="col-md-9">
                        <livewire:scheduling.recent-schedules />
                    </div>

                    <div class="col-md-3">
                        <div class="card card-animate p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0"><i class="ti ti-calendar me-1"></i> Calendar</h6>
                                <small class="text-muted fw-semibold">{{ $month->format('F Y') }}</small>
                            </div>
                            <div class="scheduling-calendar text-center small">
                                @foreach (['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $weekday)
                                    <div class="text-muted fw-semibold">{{ $weekday }}</div>
                                @endforeach
                                @for ($i = 0; $i < $leadingBlanks; $i++)
                                    <div></div>
                                @endfor
                                @for ($day = 1; $day <= $days; $day++)
                                    <div @class(['day', 'today' => $day === now()->day])>{{ $day }}</div>
                                @endfor
                            </div>
                            @if ($term)
                                <hr>
                                <small class="text-muted">Active term</small>
                                <div class="fw-semibold">{{ $term->label }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .scheduling-calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .scheduling-calendar .day {
            padding: 4px 0;
            border-radius: 6px;
        }

        .scheduling-calendar .day.today {
            background-color: #04401f;
            color: #fff;
            font-weight: 600;
        }

        .spin {
            animation: scheduling-spin 1s linear infinite;
        }

        @keyframes scheduling-spin {
            to { transform: rotate(360deg); }
        }
    </style>
@endpush
