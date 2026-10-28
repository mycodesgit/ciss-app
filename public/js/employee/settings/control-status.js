/**
 * On/Off switches: Settings > Control Status (control/aeqf.blade.php) and Campuses Control (camp/listcamp.blade.php).
 * One delegated handler saves any switch (.js-control-toggle), so it keeps working after wire:navigate.
 *
 * Switch:  <input type="checkbox" class="js-control-toggle" data-url="PUT url" data-label="Popup title">
 * Badge:   <span class="badge js-control-badge" data-on="On" data-off="Off">   (texts optional)
 * Server:  receives { status: 1|0 }, answers { is_on, message }
 */
(function () {
    'use strict';

    function setBadge(input, isOn) {
        const $badge = $(input).closest('.card').find('.js-control-badge');

        $badge
            .toggleClass('bg-success', isOn)
            .toggleClass('bg-danger', !isOn)
            .text(isOn ? ($badge.data('on') || 'On') : ($badge.data('off') || 'Off'));
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
