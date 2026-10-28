@extends('layouts.master_empdash')

@section('title')
    CISS - Dashboard
@endsection

@section('workspace')
    @php
        $employee = Auth::guard('web')->user();
    @endphp

    <div class="row g-4 mb-4" style="padding-left: 20px; padding-right: 20px">
        @foreach ($stats as $stat)
            <div class="col-xl-3 col-md-6 col-12">
                <div class="card card-animate h-100">
                    <div class="card-body p-6">
                        <div class="d-flex justify-content-between pb-2">
                            <div>
                                <h3 class="fw-bold h4">{{ number_format($stat['value']) }}</h3>
                                <span>{{ $stat['label'] }}</span>
                            </div>
                            <div>
                                <i class="{{ $stat['icon'] }} fs-1 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-5" style="padding-left: 20px; padding-right: 20px">
        <div class="col-lg-4 col-12">
            <div class="card card-animate mb-3">
                <div class="card-body p-6">
                    <div class="d-flex justify-content-between pb-2">
                        <div>
                            <h3 class="fw-bold h4">VISION</h3>
                            <span>CPSU as the leading technology-driven multi-disciplinary University by 2030.</span>
                        </div>
                        <div>
                            <i class="ti ti-eye fs-1 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-animate mb-3">
                <div class="card-body p-6">
                    <div class="d-flex justify-content-between pb-2">
                        <div>
                            <h3 class="fw-bold h4">MISSION</h3>
                            <span>CPSU is committed to produce competent graduates who can generate and extend leading technologies in multi-disciplinary areas beneficial to the community.</span>
                        </div>
                        <div>
                            <i class="ti ti-target fs-1 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-animate mb-3">
                <div class="card-body p-6">
                    <div class="d-flex justify-content-between pb-2">
                        <div>
                            <h3 class="fw-bold h4">GOAL</h3>
                            <span>To provide efficient, Quality, Technology-driven and Gender-Sensitive Products and Services.</span>
                        </div>
                        <div>
                            <i class="ti ti-clipboard fs-1 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-animate mb-4">
                <div class="card-header d-flex justify-content-between align-items-center px-4 py-3">
                    <h4 class="mb-0 h5">
                        Current Enrollment A.Y. {{ $term['academic_year'] }}, {{ $term['semester'] }}
                    </h4>
                    <span class="text-success">{{ $term['status'] }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Campus</th>
                                    <th class="text-end">Enrolled</th>
                                    <th class="text-end" style="width: 20%">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($campusEnrollment as $code => $count)
                                    <tr @class(['fw-bold text-success' => $code === $employee->campus])>
                                        <td>{{ config('ciss.campuses')[$code] ?? $code }}</td>
                                        <td class="text-end">{{ number_format($count) }}</td>
                                        <td class="text-end">{{ number_format($count / $totalEnrolled * 100, 1) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th class="text-end">{{ number_format($totalEnrolled) }}</th>
                                    <th class="text-end">100%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card card-animate h-100">
                        <div class="card-header d-flex justify-content-between align-items-center px-4 py-3">
                            <h4 class="mb-0 h5">Academic Calendar</h4>
                            <i class="ti ti-calendar-event fs-4 text-success"></i>
                        </div>
                        <div class="card-body">
                            <table class="table mb-0">
                                <tbody>
                                    @foreach ($calendar as $event)
                                        <tr>
                                            <th>
                                                {{ $event['title'] }}
                                                <br>
                                                <small class="fw-normal" style="font-style: italic">{{ $event['date'] }}</small>
                                            </th>
                                            <td class="text-end">
                                                @if ($event['status'] === 'done')
                                                    <i class="text-success">Done</i>
                                                @elseif ($event['status'] === 'current')
                                                    <i class="text-warning">Ongoing</i>
                                                @else
                                                    <i class="text-danger">Upcoming</i>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-animate h-100">
                        <div class="card-header d-flex justify-content-between align-items-center px-4 py-3">
                            <h4 class="mb-0 h5">Announcements</h4>
                            <i class="ti ti-speakerphone fs-4 text-success"></i>
                        </div>
                        <div class="card-body">
                            <table class="table mb-0">
                                <tbody>
                                    @foreach ($announcements as $announcement)
                                        <tr>
                                            <td>
                                                {{ $announcement['title'] }}
                                                <br>
                                                <small style="font-style: italic">{{ $announcement['source'] }} - {{ $announcement['date'] }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
