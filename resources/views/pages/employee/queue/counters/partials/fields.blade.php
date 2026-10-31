{{-- Personnel and category of a counter (add / edit modals). The personnel options come with the table data (queue/counters.js) --}}
<div class="col-md-12">
    <label class="form-label fw-semibold">Personnel: <span class="text-danger">*</span></label>
    <select name="useridlog" class="form-control js-personnel" required>
        <option value="" selected disabled>Select</option>
    </select>
</div>
<div class="col-md-12">
    <label class="form-label fw-semibold">Category: <span class="text-danger">*</span></label>
    <select name="category" class="form-control" required>
        <option value="" selected disabled>Select</option>
        @foreach ($categories as $category)
            <option value="{{ $category }}">{{ $category }}</option>
        @endforeach
    </select>
</div>
