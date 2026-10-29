{{-- Schedule: exam date/time (from this year's schedules of your campus) and venue --}}
<x-employee.form-modal id="scheduleModal" title="Assign Exam Schedule" form-id="scheduleForm" url="schedule-url" method="PUT">
    <label>Applicant:</label>
    <input type="text" class="form-control form-control-sm mb-3" data-show="display_name" readonly>

    <label>Current Schedule:</label>
    <input type="text" class="form-control form-control-sm mb-3" data-show="schedule_label" data-empty="No schedule yet" readonly>

    <label>Exam Date & Time: <span class="text-danger">*</span></label>
    <select name="dateID" class="form-control form-control-sm mb-3" required>
        <option value="" disabled selected>Select schedule</option>
        @foreach ($schedules as $schedule)
            <option value="{{ $schedule['id'] }}" data-full="{{ $schedule['remaining'] === 0 ? 1 : 0 }}">
                {{ $schedule['label'] }} ({{ $schedule['remaining'] === 0 ? 'Full' : $schedule['remaining'] . ' slots left' }})
            </option>
        @endforeach
    </select>

    <label>Venue: <span class="text-danger">*</span></label>
    <select name="venue" class="form-control form-control-sm" required>
        <option value="" disabled selected>Select venue</option>
        @foreach ($venues as $venue)
            <option value="{{ $venue }}">{{ $venue }}</option>
        @endforeach
    </select>
</x-employee.form-modal>
