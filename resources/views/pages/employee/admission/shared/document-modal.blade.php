{{-- School ID / Report Card / Proof: shows the uploaded image (or a link for PDFs) --}}
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted small mb-2 js-document-note"></p>
                <img class="img-fluid rounded js-document-image d-none" alt="Uploaded document">
                <a class="btn btn-success btn-sm js-document-link d-none" target="_blank" rel="noopener">
                    <i class="fas fa-file-pdf"></i> Open document
                </a>
                <p class="text-muted fs-5 my-5 js-document-empty d-none">No document uploaded.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
