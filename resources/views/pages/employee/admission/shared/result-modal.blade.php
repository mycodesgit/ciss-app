{{--
    Exam result (List of Examinee: Assign Result, Examination Results: Update Test Result).
    Only the raw score is sent; stanine and remarks are a preview here and computed again on the server
    (config('ciss.stanines') / 'stanine_remarks' / 'passing_scores').
    $withQualifier adds the "Second Qualifier" checkbox, shown when the remark is Failed.
--}}
<x-employee.form-modal id="resultModal" title="{{ $title ?? 'Assign Result to Examinee' }}" form-id="resultForm" url="result-url" method="PUT">
    <label>Examinee:</label>
    <input type="text" class="form-control form-control-sm mb-3" data-show="display_name" readonly>

    <div class="form-group">
        <label>Raw Score: <span class="text-danger">*</span></label>
        <input type="number" name="raw_score" class="form-control form-control-sm js-raw-score" min="1" max="{{ $maxRawScore }}" required
            data-stanines='@json($stanines)' data-remarks='@json($stanineRemarks)' data-passing-score="{{ $passingScore }}">
        <small class="text-muted">
            From 1 to {{ $maxRawScore }}.
            @if ($passingScore)
                Passing score for your campus: {{ $passingScore }}.
            @endif
        </small>
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

    @if ($withQualifier ?? false)
        <div class="card mt-3 mb-0 js-qualifier-group d-none" style="background-color: #e9ecef;">
            <div class="card-body py-2 px-3">
                <div class="icheck-warning">
                    <input type="checkbox" id="qualifier" name="second_qualifier" value="1">
                    <label for="qualifier">
                        <h6 class="mb-0">Yes, Second Qualifier</h6>
                    </label>
                </div>
                <small class="text-muted">A failed examinee marked as Second Qualifier can still be pushed to the Confirm list.</small>
            </div>
        </div>
    @endif
</x-employee.form-modal>
