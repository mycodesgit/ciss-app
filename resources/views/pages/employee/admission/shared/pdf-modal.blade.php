{{--
    Modal that shows a PDF route (dompdf) in an iframe, in the browser's PDF viewer (zoom, download, print).
    @include with ['id' => 'modal id', 'title' => 'heading']; opened by AdmissionUi.openPdf() in admission/common.js
--}}
<div class="modal fade js-pdf-modal" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">
                    <i class="fas fa-file-pdf text-danger me-1"></i> {{ $title }}
                    <small class="text-muted fw-normal js-pdf-name"></small>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 position-relative" style="height: 75vh; background-color: #e9ecef;">
                <div class="position-absolute top-50 start-50 translate-middle text-muted js-pdf-loading">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Loading...
                </div>
                <iframe class="w-100 h-100 border-0 position-relative js-pdf-frame" title="{{ $title }}"></iframe>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto">Use the viewer's <i class="fas fa-download"></i> download or <i class="fas fa-print"></i> print buttons above.</small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
