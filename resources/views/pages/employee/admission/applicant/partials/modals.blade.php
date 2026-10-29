{{-- Modals of List of Applicant (filled and opened by applicants.js) --}}

@include('pages.employee.admission.shared.info-modal')
@include('pages.employee.admission.shared.schedule-modal')
@include('pages.employee.admission.shared.document-modal')

{{-- Re-Upload: which documents the applicant must upload again --}}
<x-employee.form-modal id="reuploadModal" title="Re-Upload Access" form-id="reuploadForm" url="reupload-url" method="PUT">
    <label>Applicant:</label>
    <input type="text" class="form-control form-control-sm mb-3" data-show="display_name" readonly>

    <label>Allow the applicant to re-upload:</label>
    @foreach ($reuploadDocuments as $value => $label)
        <div class="icheck-success">
            <input type="checkbox" id="reupload-{{ $value }}" name="reuploadallow[]" value="{{ $value }}">
            <label for="reupload-{{ $value }}">{{ $label }}</label>
        </div>
    @endforeach
</x-employee.form-modal>
