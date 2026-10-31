/**
 * Enrollment > Deleted Enrollment Logs (resources/views/pages/employee/enrollment/deleted-enrollment-logs/index.blade.php).
 * Server: DeletedEnrollmentLogsController. The table and wire:navigate handling come from CissCrud
 * (public/js/employee/crud.js); searching reloads the table, View opens the Enrollment Logs PDF of the deleted enrollment.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const form = () => document.getElementById('deletedEnrollmentLogsSearchForm');

    // Filters of the last table load, so the export title matches the rows on screen
    let shownFilters = {};
    function loadFilters() {
        const elements = form().elements;
        shownFilters = { campus: elements.campus.value, schlyear: elements.schlyear.value, semester: elements.semester.value };
        return shownFilters;
    }

    function shownSearch() {
        if (!shownFilters.semester) return '';
        const label = (select, value) => (select.querySelector('option[value="' + value + '"]') || {}).textContent || '';
        return label(form().elements.semester, shownFilters.semester) + ' ' + shownFilters.schlyear + ' (' + shownFilters.campus + ')';
    }

    const column = (data) => ({ data, render: text, defaultContent: '' });

    CissCrud.register({
        pageId: 'deletedEnrollmentLogs',
        table: '#deletedEnrollmentLogsTable',
        order: [[0, 'desc']],
        ajaxData: loadFilters,
        columns: [
            // Shown as "May 09, 2026 08:16 PM", sorted by the date and time
            { data: 'deleted', render: (deleted, type, row) => type === 'sort' ? (row.deleted_sort || '') : CissCrud.escapeHtml(deleted || ''), defaultContent: '' },
            column('studentID'),
            column('name'),
            column('gender'),
            column('course'),
            column('deletedBy'),
            {
                data: 'logs_url',
                orderable: false,
                searchable: false,
                // No saved enrollment in the logs (e.g. enrolled before the logs were kept): nothing to view
                render: (url) => url
                    ? '<button type="button" class="btn btn-primary btn-sm js-view-logs" title="What the enrollment was"><i class="fas fa-eye"></i> View</button>'
                    : '<span class="text-muted small">No logs</span>',
            },
        ],
        tableOptions: {
            language: { emptyTable: 'No deleted enrollments to show. Select a campus, school year and semester, then click Search.' },
            responsive: false,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: () => 'Deleted Enrollment Logs - ' + shownSearch(),
                exportOptions: { columns: ':not(:last-child)' },
            }],
            drawCallback: function () {
                const total = this.api().data().count();
                $('#deletedEnrollmentLogs .js-search-shown').text(shownFilters.semester ? shownSearch() + ' · ' + total + ' deleted' : '');
            },
        },
    });

    $(document).on('submit', '#deletedEnrollmentLogsSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    $(document).on('click', '#deletedEnrollmentLogsTable .js-view-logs', function () {
        const row = $('#deletedEnrollmentLogsTable').DataTable().row($(this).closest('tr')).data();
        if (row) CissCrud.openPdf('#deletedEnrollmentLogsPdfModal', row.studentID + ' ' + row.name, row.logs_url);
    });
})();
