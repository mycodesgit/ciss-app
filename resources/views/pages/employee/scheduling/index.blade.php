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
                        {{-- Active term setup in the user's campus (cached 5 minutes by the controller) --}}
                        <div class="card card-animate h-100">
                            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3">
                                <div>
                                    <h6 class="card-title mb-0"><i class="ti ti-checklist"></i> Term Setup</h6>
                                    <small class="text-muted">{{ $term ? "{$term->label} · {$campusName}" : 'No active term (Accounts > A.Y. & Semester)' }}</small>
                                </div>
                                @if ($setup && $canOffer)
                                    <a href="{{ route('employee.scheduling.offerings.index') }}" wire:navigate class="btn btn-success btn-sm text-light">
                                        <i class="ti ti-books"></i> Subject Offered
                                    </a>
                                @endif
                            </div>
                            <div class="card-body">
                                @if ($setup)
                                    @php($percent = $setup['sections'] ? (int) round($setup['ready'] / $setup['sections'] * 100) : 0)
                                    <div class="row g-3 text-center mb-3">
                                        @foreach (['Sections' => $setup['sections'], 'Subjects Offered' => $setup['offerings'], 'Sections with Subjects' => $setup['ready'], 'Designations' => $setup['designations']] as $label => $value)
                                            <div class="col-6 col-md-3">
                                                <div class="border rounded py-2">
                                                    <div class="fw-bold fs-4">{{ number_format($value) }}</div>
                                                    <small class="text-muted">{{ $label }}</small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Sections with subjects offered</span>
                                        <span class="fw-semibold">{{ $setup['ready'] }} / {{ $setup['sections'] }} ({{ $percent }}%)</span>
                                    </div>
                                    <div class="progress mb-3" style="height: 8px;" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar bg-success" style="width: {{ $percent }}%"></div>
                                    </div>

                                    @if ($setup['sections'] === 0)
                                        <p class="text-muted mb-0">No sections yet for this term. Add them in Classes Enrolled.</p>
                                    @elseif ($setup['withoutSubjects'])
                                        <h6 class="fw-semibold small mb-2">Sections without subjects yet</h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach (array_slice($setup['withoutSubjects'], 0, $sectionsShown) as $section)
                                                <span class="badge bg-warning-subtle text-warning-emphasis border">{{ $section }}</span>
                                            @endforeach
                                            @if (count($setup['withoutSubjects']) > $sectionsShown)
                                                <span class="badge bg-light text-muted border">and {{ count($setup['withoutSubjects']) - $sectionsShown }} more</span>
                                            @endif
                                        </div>
                                    @else
                                        <p class="text-success mb-0"><i class="ti ti-circle-check"></i> Every section has subjects offered.</p>
                                    @endif
                                @else
                                    <p class="text-muted mb-0">Set an active term to see its setup.</p>
                                @endif
                            </div>
                        </div>
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

    </style>
@endpush
