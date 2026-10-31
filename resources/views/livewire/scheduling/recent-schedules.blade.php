{{-- Scheduling dashboard: latest schedules plotted (App\Livewire\Scheduling\RecentSchedules), refreshed every minute while visible --}}
<div class="card card-animate h-100" wire:ignore.self wire:poll.60s.visible>
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3">
        <div>
            <h6 class="card-title mb-0"><i class="ti ti-calendar-time"></i> Recent Schedules Plotted</h6>
            <small class="text-muted">
                {{ $term ? $term->label : 'No active term (Accounts > A.Y. & Semester)' }}
            </small>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if (count($campuses) > 1)
                <select class="form-select form-select-sm" wire:model.live="campus" aria-label="Campus">
                    @foreach ($campuses as $code => $name)
                        <option value="{{ $code }}">{{ str_replace(' Campus', '', $name) }}</option>
                    @endforeach
                </select>
            @endif
            <button type="button" class="btn btn-outline-success btn-sm text-nowrap" wire:click="$refresh" wire:loading.attr="disabled" title="Refresh">
                <i class="ti ti-refresh" wire:loading.class="spin"></i>
            </button>
        </div>
    </div>
    <div class="card-body" wire:loading.class="opacity-50">
        @error('campus') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Subject & Section</th>
                        <th>Faculty</th>
                        <th>Room</th>
                        <th>Schedule</th>
                        <th>Posted By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $schedule)
                        <tr wire:key="schedule-{{ $schedule->id }}">
                            <td>{{ $schedule->sub_name }} <small class="text-muted d-block">{{ $schedule->subSec }}</small></td>
                            <td>{{ $schedule->lname ? "{$schedule->lname}, {$schedule->fname}" : '—' }}</td>
                            <td>{{ $schedule->room_name ?? '—' }}</td>
                            <td>{{ $schedule->schedday }} <small class="text-muted d-block">{{ $schedule->start_time }}{{ $schedule->end_time ? " – {$schedule->end_time}" : '' }}</small></td>
                            <td>{{ $schedule->postedBy }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No schedules plotted yet in this campus and term.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
