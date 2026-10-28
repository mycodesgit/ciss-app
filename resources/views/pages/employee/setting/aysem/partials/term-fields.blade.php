{{-- Fields shared by the Add and Edit A.Y. & Semester forms --}}
<div class="form-group">
    <label>School Year: <span class="text-danger">*</span></label>
    <input type="text" name="schlyear" class="form-control form-control-sm" placeholder="e.g. 2025-2026" pattern="\d{4}-\d{4}" title="Format: 2025-2026" required>
</div>

<div class="form-group mt-3">
    <label>Semester: <span class="text-danger">*</span></label>
    <select name="semester" class="form-control form-control-sm" required>
        @foreach ($semesters as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="form-group mt-3">
    <label>Status: <span class="text-danger">*</span></label>
    <select name="set_status" class="form-control form-control-sm" required>
        @foreach ($statuses as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
