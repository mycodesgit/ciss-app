/**
 * Enrollment > Number of Enrollees (resources/views/pages/employee/enrollment/number-of-enrollees/index.blade.php).
 * Server: NumberOfEnrolleesController. Search a campus and term -> the total enrolled and one row per year level.
 * Listeners are on document, so they work after wire:navigate.
 */
(function () {
    'use strict';

    const escape = (text) => CissCrud.escapeHtml(text);
    const page = () => document.getElementById('numberOfEnrollees');
    const YEAR_NAMES = { 1: 'First Year', 2: 'Second Year', 3: 'Third Year', 4: 'Fourth Year', 5: 'Fifth Year' };

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
        $page.find('.js-year-rows').html(response.years.length
            ? response.years.map((year) => '<tr>' +
                '<td>' + escape(YEAR_NAMES[year.year] || year.year || 'No year level') + '</td>' +
                '<td>' + year.male + '</td>' +
                '<td>' + year.female + '</td>' +
                '<td><strong>' + year.total + '</strong></td>' +
                '</tr>').join('')
            : '<tr><td colspan="4" class="text-center text-muted">No enrolled students in this term.</td></tr>');
        $page.find('.js-result').removeClass('d-none');
    }

    $(document).on('submit', '#numberOfEnrolleesSearchForm', function (e) {
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
