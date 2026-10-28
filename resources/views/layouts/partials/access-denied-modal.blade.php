{{-- Shown when a middleware sent the user back here because they opened a page they can't access --}}
@php
    $denied = session('access_denied');
@endphp

<div class="modal fade" id="accessDeniedModal" tabindex="-1" aria-labelledby="accessDeniedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accessDeniedModalLabel">
                    <i class="fas fa-lock text-danger me-2"></i> Access Restricted
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    You can't open <strong>{{ $denied['module'] }}</strong>.
                </p>
                <p class="text-muted small mb-3">{{ $denied['reason'] }}</p>

                <div class="card" style="background-color: #e9ecef;">
                    <div class="card-body py-2 px-3 small">
                        <i class="fas fa-circle-info text-success me-1"></i>
                        If you need this for your work, please ask the system administrator at the
                        <strong>MIS Office</strong> to grant you access.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK, got it</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        function showAccessDenied() {
            $('#accessDeniedModal').modal('show');
        }

        // On a full page load Bootstrap's jQuery plugins are registered at DOMContentLoaded
        document.readyState === 'loading'
            ? document.addEventListener('DOMContentLoaded', showAccessDenied)
            : showAccessDenied();
    })();
</script>
