{{-- campus[] checkboxes (Colleges, Programs); checked by CissScheduling.checkCampuses(); none checked is sent empty by crud.js --}}
<div class="row g-2">
    @foreach ($campuses as $code => $name)
        <div class="col-6 col-md-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="campus[]" value="{{ $code }}" id="campus-{{ $code }}">
                <label class="form-check-label" for="campus-{{ $code }}">{{ str_replace(' Campus', '', $name) }}</label>
            </div>
        </div>
    @endforeach
</div>
