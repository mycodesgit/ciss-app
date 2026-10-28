{{--
    Modal with a form submitted by AJAX.
    url    = key of the URL in the page's data-* attributes (e.g. "update-url"), may contain __ID__
    method = HTTP method sent to Laravel (POST, PUT)
--}}
@props(['id', 'title', 'formId', 'url', 'method' => 'POST', 'size' => 'md'])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-{{ $size }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="{{ $formId }}" data-url="{{ $url }}" data-method="{{ $method }}">
                <div class="modal-body">
                    {{ $slot }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
