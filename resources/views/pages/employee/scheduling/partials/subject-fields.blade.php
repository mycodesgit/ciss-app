{{--
    Subject, units and fees of a curriculum / offering form (scheduling/common.js: subjectPicker, previewFees).
    Units and fees are read-only previews: the server computes the saved values. Graduate school subjects unlock the
    lecture fee. Needs $funds ([id => label]).
--}}
<div class="col-md-12">
    <label class="form-label fw-semibold">Subject: <span class="text-danger">*</span></label>
    <select class="form-control form-control-sm js-subject" name="subCode" required></select>
</div>

<div class="col-md-4">
    <label class="form-label fw-semibold">Lecture Credit:</label>
    <input type="number" name="lecUnit" class="form-control form-control-sm" readonly tabindex="-1">
</div>
<div class="col-md-4">
    <label class="form-label fw-semibold">Laboratory Credit:</label>
    <input type="number" name="labUnit" class="form-control form-control-sm" readonly tabindex="-1">
</div>
<div class="col-md-4">
    <label class="form-label fw-semibold">Total Credit:</label>
    <input type="number" name="subUnit" class="form-control form-control-sm" readonly tabindex="-1">
</div>

<div class="col-md-4">
    <label class="form-label fw-semibold">Lecture Fee:</label>
    <input type="number" name="lecFee" class="form-control form-control-sm js-fee-input" min="0" step="0.01" value="0" readonly>
    <small class="text-muted">Typed for graduate school subjects only.</small>
</div>
<div class="col-md-4">
    <label class="form-label fw-semibold">Laboratory Fee:</label>
    <input type="number" name="labFee" class="form-control form-control-sm" readonly tabindex="-1">
</div>
<div class="col-md-4">
    <label class="form-label fw-semibold">Development Fee:</label>
    <input type="number" name="devFee" class="form-control form-control-sm" readonly tabindex="-1">
</div>

<div class="col-md-4">
    <label class="form-label fw-semibold">OJT/Thesis: <span class="text-danger">*</span></label>
    {{-- Options depend on the semester (filled by the page script) --}}
    <select class="form-control form-control-sm js-ojt js-fee-input" name="isOJT" required></select>
</div>
<div class="col-md-4">
    <label class="form-label fw-semibold">Fund: </label>
    <select class="form-control form-control-sm" name="fund">
        <option value="">No Account</option>
        @foreach ($funds as $id => $label)
            <option value="{{ $id }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-4">
    <label class="form-label fw-semibold">IT Subject: <span class="text-danger">*</span></label>
    <select class="form-control form-control-sm" name="itfee" required>
        <option value="No">No</option>
        <option value="Yes">Yes, IT Subject</option>
    </select>
</div>
<div class="col-md-4">
    <label class="form-label fw-semibold">Template: <span class="text-danger">*</span></label>
    <select class="form-control form-control-sm" name="isTemp" required>
        <option value="Yes">Yes</option>
        <option value="No">No</option>
    </select>
</div>
