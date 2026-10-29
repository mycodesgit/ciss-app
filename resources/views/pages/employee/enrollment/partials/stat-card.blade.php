{{-- One count card of the Enrollment dashboard. With a $group, clicking it opens that student list (PDF) --}}
<div @class(['card card-animate', 'js-student-list' => $group])
    @if ($group) data-group="{{ $group }}" data-title="{{ $groups[$group][0] }}" title="View the list of {{ strtolower($groups[$group][0]) }}" @endif>
    <div class="card-body p-6">
        <div class="d-flex justify-content-between pb-2">
            <div>
                <h3 class="fw-bold h1">{{ number_format((int) $count) }}</h3>
                <span>{{ $label }}</span>
            </div>
            <div>
                <i @class(['ti fs-1 text-success', 'ti-list-details' => $group, 'ti-users' => ! $group])></i>
            </div>
        </div>
    </div>
</div>
