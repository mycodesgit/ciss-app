{{-- User level select. Pass 'name' for an editable field, or omit it for a read-only display (class js-user-role). --}}
<select @if (!empty($name)) name="{{ $name }}" required @else disabled @endif class="form-control form-control-sm {{ empty($name) ? 'js-user-role' : '' }}">
    <option value="" disabled selected>Level</option>
    @foreach ($roles as $value => $label)
        <option value="{{ $value }}">{{ $label }}</option>
    @endforeach
</select>
