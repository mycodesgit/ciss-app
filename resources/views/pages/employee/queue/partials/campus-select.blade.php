{{-- Campus of a Queue page ($campuses, $campus): changing it reloads the page for that campus (?campus=) --}}
<select class="form-control form-control-sm js-queue-campus" style="width: auto;" aria-label="Campus"
    onchange="window.Livewire ? Livewire.navigate('{{ url()->current() }}?campus=' + encodeURIComponent(this.value)) : (location.search = '?campus=' + encodeURIComponent(this.value))">
    @foreach ($campuses as $code => $name)
        <option value="{{ $code }}" @selected($code === $campus)>{{ str_replace(' Campus', '', $name) }}</option>
    @endforeach
</select>
