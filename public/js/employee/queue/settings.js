/**
 * Queue > Settings (resources/views/pages/employee/queue/settings/index.blade.php). Server: QueueSettingController.
 * Listeners are on document, so they work after wire:navigate.
 */
(function () {
    'use strict';

    const page = () => document.getElementById('queueSettings');

    function post(url, data) {
        return $.ajax({ url, type: 'POST', data, headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    }

    function errorMessage(xhr, fallback) {
        const json = xhr.responseJSON || {};
        return json.errors ? Object.values(json.errors)[0][0] : (json.message || fallback);
    }

    $(document).on('change', '#queueSettings .js-queue-toggle', function () {
        const input = this;

        post(page().dataset.toggleUrl, { _method: 'PUT', status: input.checked ? 1 : 0 })
            .done(function (response) {
                $('#queueSettings .js-queue-state').text(response.is_on ? 'On' : 'Off');
                toastr.success(response.message);
            })
            .fail(function (xhr) {
                input.checked = !input.checked; // not saved: put the switch back
                toastr.error(errorMessage(xhr, 'Could not change the queueing mode.'));
            });
    });

    $(document).on('click', '#queueSettings .js-queue-reset', function () {
        const $button = $(this);

        Swal.fire({
            title: 'Reset the queue of ' + page().dataset.campusName + '?',
            text: 'Every number goes back to waiting and every window serves nothing. This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, reset it',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $button.prop('disabled', true);

            post(page().dataset.resetUrl, {})
                .done((response) => Swal.fire({ icon: 'success', title: 'Queue reset', text: response.message }))
                .fail((xhr) => Swal.fire({ icon: 'error', title: 'Not reset', text: errorMessage(xhr, 'Could not reset the queue.') }))
                .always(() => $button.prop('disabled', false));
        });
    });
})();
