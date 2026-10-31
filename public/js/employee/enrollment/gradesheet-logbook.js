/**
 * Enrollment > Gradesheet Logbook (resources/views/pages/employee/enrollment/gradesheet-logbook/index.blade.php).
 * Server: GradesheetLogbookController. The table and wire:navigate handling come from CissCrud
 * (public/js/employee/crud.js); searching reloads the table, Logbook PDF opens the PDF of the last search.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const page = () => document.getElementById('gradesheetLogbook');
    const form = () => document.getElementById('gradesheetLogbookSearchForm');

    // Filters of the last table load, so the export title and the PDF match the rows on screen
    let shownFilters = {};
    function loadFilters() {
        const elements = form().elements;
        shownFilters = { campus: elements.campus.value, schlyear: elements.schlyear.value, semester: elements.semester.value, college: elements.college.value };
        return shownFilters;
    }

    const searched = () => Boolean(shownFilters.semester && shownFilters.college);

    function shownSearch() {
        if (!searched()) return '';
        const label = (select, value) => (select.querySelector('option[value="' + value + '"]') || {}).textContent || '';
        return shownFilters.college + ' - ' + label(form().elements.semester, shownFilters.semester) + ' ' + shownFilters.schlyear + ' (' + shownFilters.campus + ')';
    }

    const column = (data) => ({ data, render: text, defaultContent: '' });

    CissCrud.register({
        pageId: 'gradesheetLogbook',
        table: '#gradesheetLogbookTable',
        order: [[6, 'desc']],
        ajaxData: loadFilters,
        columns: [
            column('term'),
            column('faculty'),
            column('subject'),
            column('title'),
            column('section'),
            column('college'),
            // Shown as "Apr 19, 2026 08:41 PM", sorted by the date and time
            { data: 'submitted', render: (submitted, type, row) => type === 'sort' ? (row.submitted_sort || '') : CissCrud.escapeHtml(submitted || ''), defaultContent: '' },
        ],
        tableOptions: {
            language: { emptyTable: 'No submitted gradesheets to show. Select a campus, school year, semester and college, then click Search.' },
            responsive: false,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: () => 'Gradesheet Logbook - ' + shownSearch(),
            }],
            drawCallback: function () {
                const total = this.api().data().count();
                $('#gradesheetLogbook .js-search-shown').text(searched() ? shownSearch() + ' · ' + total + ' submitted' : '');
                $('#gradesheetLogbookPdfButton').prop('disabled', !searched());
            },
        },
    });

    $(document).on('submit', '#gradesheetLogbookSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    $(document).on('click', '#gradesheetLogbookPdfButton', function () {
        if (!searched()) return;
        CissCrud.openPdf('#gradesheetLogbookPdfModal', shownSearch(), page().dataset.pdfUrl + '?' + $.param(shownFilters));
    });
})();
