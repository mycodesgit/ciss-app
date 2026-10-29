{{-- Slot cards of Availability Slots (AdmissionSlotsController@list), loaded into #slot-container --}}
@php use Carbon\Carbon; @endphp

@forelse ($groupedSlots as $date => $slots)
    @php $day = Carbon::parse($date); @endphp

    <div class="col-12 col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center pt-3">
                <div>
                    <h6 class="card-title mb-0">
                        <i class="ti ti-calendar"></i> Admission Date: {{ $day->format('F d, Y') }}
                    </h6>
                    <small class="text-muted">{{ $day->format('l') }}</small>
                </div>

                {{-- Live for today and upcoming dates, Done for past dates --}}
                @if ($day->isPast() && ! $day->isToday())
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill small">Done</span>
                @else
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                        <i class="ti ti-circle-filled me-1 blink-dot"></i>Live
                    </span>
                @endif
            </div>
            <div class="card-body p-2">
                <table class="table table-striped table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Availability</th>
                            <th>Campus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($slots as $slot)
                            <tr>
                                <td class="fw-semibold">{{ Carbon::parse($slot->time)->format('h:i A') }}</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success" title="Booked">{{ $slot->booked }}</span>
                                    |
                                    <span class="badge bg-info-subtle text-info" title="Slots">{{ $slot->slots }}</span>
                                    @if ($slot->remaining === 0)
                                        <span class="badge bg-danger-subtle text-danger">Full</span>
                                    @else
                                        <small class="text-muted">({{ $slot->remaining }} left)</small>
                                    @endif
                                </td>
                                <td>{{ $campusName }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="small text-muted">
                            <td>Total</td>
                            <td colspan="2">{{ $slots->sum('booked') }} of {{ $slots->sum('slots') }} booked &middot; {{ $slots->sum('remaining') }} left</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="text-center text-muted py-5">
            <i class="ti ti-calendar-off fs-1 d-block mb-2"></i>
            No examination schedules for {{ $campusName }} in this year.
        </div>
    </div>
@endforelse
