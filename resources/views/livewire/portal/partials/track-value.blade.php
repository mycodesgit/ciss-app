@if ($text !== null && $text !== '')
    <i class="{{ ($danger ?? false) ? 'text-danger' : 'text-success' }}">{{ $text }}</i>
@else
    <i class="text-danger">Waiting</i>
@endif
