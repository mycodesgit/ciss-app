/**
 * Enrollment > CWTS/LTS/ROTC (resources/views/pages/employee/enrollment/nstp-enrollees/index.blade.php).
 * Server: NstpEnrolleesController. Search a campus and term -> one row per NSTP component and the total.
 * Listeners are on document, so they work after wire:navigate.
 */
(function () {
    'use strict';

    const escape = (text) => CissCrud.escapeHtml(text);
    const page = () => document.getElementById('nstpEnrollees');

    function errorMessage(xhr, fallback) {
        const json = xhr.responseJSON || {};
        return json.errors ? Object.values(json.errors)[0][0] : (json.message || fallback);
    }

    function showCounts(response) {
        const $page = $(page());

        $page.find('.js-campus').text(response.campus);
        $page.find('.js-term').text(response.term);
        $page.find('.js-total').text(response.total);
        $page.find('.js-male').text(response.male);
        $page.find('.js-female').text(response.female);
        $page.find('.js-component-rows').html(response.components.map((row) => '<tr>' +
            '<td>' + escape(row.component) + '</td>' +
            '<td>' + row.male + '</td>' +
            '<td>' + row.female + '</td>' +
            '<td><strong>' + row.total + '</strong></td>' +
            '</tr>').join(''));
        $page.find('.js-result').removeClass('d-none');
    }

    $(document).on('submit', '#nstpEnrolleesSearchForm', function (e) {
        e.preventDefault();
        const $submit = $(this).find('[type="submit"]').prop('disabled', true);

        $.get(page().dataset.countUrl, $(this).serialize())
            .done(showCounts)
            .fail(function (xhr) {
                $(page()).find('.js-result').addClass('d-none');
                toastr.error(errorMessage(xhr, 'Could not load the number of enrollees.'));
            })
            .always(() => $submit.prop('disabled', false));
    });
})();
