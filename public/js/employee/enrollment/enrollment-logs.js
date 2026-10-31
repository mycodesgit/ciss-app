/**
 * Enrollment > Enrollment Logs (resources/views/pages/employee/enrollment/enrollment-logs/index.blade.php).
 * Server: EnrollmentLogsController. The table and wire:navigate handling come from CissCrud
 * (public/js/employee/crud.js); searching reloads the table, View opens the student's logs PDF in the modal.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const form = () => document.getElementById('enrollmentLogsSearchForm');

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
        pageId: 'enrollmentLogs',
        table: '#enrollmentLogsTable',
        order: [[0, 'desc']],
        ajaxData: loadFilters,
        columns: [
            // Shown as "Aug 27, 2026 08:54 PM", sorted by the date and time
            { data: 'saved', render: (saved, type, row) => type === 'sort' ? (row.saved_sort || '') : CissCrud.escapeHtml(saved || ''), defaultContent: '' },
            column('studentID'),
            column('name'),
            column('gender'),
            column('course'),
            { data: 'saves', className: 'text-center', defaultContent: '' },
            column('postedBy'),
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => '<button type="button" class="btn btn-primary btn-sm js-view-logs" title="View the logs"><i class="fas fa-eye"></i> View</button>',
            },
        ],
        tableOptions: {
            language: { emptyTable: 'No enrollment logs to show. Select a campus, school year and semester, then click Search.' },
            responsive: false,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: () => 'Enrollment Logs - ' + shownSearch(),
                exportOptions: { columns: ':not(:last-child)' },
            }],
            drawCallback: function () {
                const total = this.api().data().count();
                $('#enrollmentLogs .js-search-shown').text(shownFilters.semester ? shownSearch() + ' · ' + total + ' student(s)' : '');
            },
        },
    });

    $(document).on('submit', '#enrollmentLogsSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    $(document).on('click', '#enrollmentLogsTable .js-view-logs', function () {
        const row = $('#enrollmentLogsTable').DataTable().row($(this).closest('tr')).data();
        if (row) CissCrud.openPdf('#enrollmentLogsPdfModal', row.studentID + ' ' + row.name, row.pdf_url);
    });
})();
