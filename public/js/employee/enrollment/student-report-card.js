/**
 * Enrollment > Student Report Card (resources/views/pages/employee/enrollment/report-card/index.blade.php).
 * Server: StudentReportCardController. The search is checked by AJAX; when the student is enrolled in that term,
 * the Report Card opens in the PDF modal (CissCrud.openPdf). The Student ID is formatted by crud.js.
 */
(function () {
    'use strict';

    function errorMessage(xhr) {
        const json = xhr.responseJSON || {};
        return json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Could not search the student.');
    }

    $(document).on('submit', '#reportCardSearchForm', function (e) {
        e.preventDefault();

        const $submit = $(this).find('[type="submit"]').prop('disabled', true);

        $.get(document.getElementById('studentReportCard').dataset.findUrl, $(this).serialize())
            .done((response) => CissCrud.openPdf('#reportCardModal', response.name + ' (' + response.term + ')', response.url))
            .fail((xhr) => toastr.error(errorMessage(xhr)))
            .always(() => $submit.prop('disabled', false));
    });
})();
