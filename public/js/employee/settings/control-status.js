/**
 * Settings > Control Status (resources/views/pages/employee/setting/control/aeqf.blade.php).
 * One delegated handler saves any switch (.js-control-toggle), so it keeps working after wire:navigate.
 */
(function () {
    'use strict';

    function setBadge(input, isOn) {
        $(input).closest('.card').find('.js-control-badge')
            .toggleClass('bg-success', isOn)
            .toggleClass('bg-danger', !isOn)
            .text(isOn ? 'On' : 'Off');
    }

    $(document).on('change', '.js-control-toggle', function () {
        const input = this;
        input.disabled = true;

        $.ajax({
            url: input.dataset.url,
            type: 'POST',
            data: { _method: 'PUT', status: input.checked ? 1 : 0 },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        })
            .done(function (response) {
                setBadge(input, response.is_on);
                Swal.fire({ icon: 'success', title: input.dataset.label, text: response.message });
            })
            .fail(function (xhr) {
                input.checked = !input.checked; // the change was not saved; put the switch back
                Swal.fire({
                    icon: 'warning',
                    title: input.dataset.label,
                    text: (xhr.responseJSON && xhr.responseJSON.message) || 'The change could not be saved. Please try again.',
                });
            })
            .always(function () {
                input.disabled = false;
            });
    });
})();
