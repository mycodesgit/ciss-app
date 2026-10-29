{{--
    Assign Result: the raw score is saved; stanine and remarks are shown as a preview here and
    computed again on the server (same table: config('ciss.stanines') / 'stanine_remarks').
--}}
<x-employee.form-modal id="resultModal" title="Assign Result to Examinee" form-id="resultForm" url="result-url" method="PUT">
    <label>Examinee:</label>
    <input type="text" class="form-control form-control-sm mb-3" data-show="display_name" readonly>

    <div class="form-group">
        <label>Raw Score: <span class="text-danger">*</span></label>
        <input type="number" name="raw_score" class="form-control form-control-sm js-raw-score" min="1" max="{{ $maxRawScore }}" required
            data-stanines='@json($stanines)' data-remarks='@json($stanineRemarks)'>
        <small class="text-muted">From 1 to {{ $maxRawScore }}.</small>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-4">
            <label>Stanine:</label>
            <input type="text" class="form-control form-control-sm js-stanine" readonly>
        </div>
        <div class="col-md-8">
            <label>Remarks:</label>
            <input type="text" class="form-control form-control-sm js-remark" readonly>
        </div>
    </div>
</x-employee.form-modal>
