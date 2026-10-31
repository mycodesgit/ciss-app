/**
 * Enrollment > Grade Encoding Logs (resources/views/pages/employee/enrollment/grade-encoding-logs/index.blade.php).
 * Server: GradeEncodingLogsController. The table and wire:navigate handling come from CissCrud
 * (public/js/employee/crud.js); searching reloads the table, Excel exports every column.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const form = () => document.getElementById('gradeEncodingLogsSearchForm');

    // Filters of the last table load, so the export title matches the rows on screen
    let shownFilters = {};
    let limited = false;
    function loadFilters() {
        const elements = form().elements;
        shownFilters = { campus: elements.campus.value, schlyear: elements.schlyear.value, semester: elements.semester.value, q: elements.q.value.trim() };
        return shownFilters;
    }

    function shownSearch() {
        if (!shownFilters.semester) return '';
        const label = (select, value) => (select.querySelector('option[value="' + value + '"]') || {}).textContent || '';
        return '"' + shownFilters.q + '" - ' + label(form().elements.semester, shownFilters.semester) + ' ' + shownFilters.schlyear + ' (' + shownFilters.campus + ')';
    }

    const column = (data) => ({ data, render: text, defaultContent: '' });

    CissCrud.register({
        pageId: 'gradeEncodingLogs',
        table: '#gradeEncodingLogsTable',
        order: [[0, 'desc']],
        ajaxData: loadFilters,
        columns: [
            // Shown as "Apr 19, 2026 08:41 PM", sorted by the date and time
            { data: 'saved', render: (saved, type, row) => type === 'sort' ? (row.saved_sort || '') : CissCrud.escapeHtml(saved || ''), defaultContent: '' },
            column('studentID'),
            column('name'),
            column('subject'),
            {
                data: 'kind',
                render: (kind, type) => type === 'display' ? CissCrud.badge(kind === 'Completion' ? 'bg-warning text-dark' : 'bg-success', kind) : kind,
                defaultContent: '',
            },
            column('fgrade'),
            column('cgrade'),
            column('encodedBy'),
        ],
        tableOptions: {
            language: { emptyTable: 'No grade logs to show. Select a campus, school year and semester, type a Student ID, name or subject, then click Search.' },
            responsive: false,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: () => 'Grade Encoding Logs - ' + shownSearch(),
            }],
            drawCallback: function () {
                const total = this.api().data().count();
                $('#gradeEncodingLogs .js-search-shown').text(shownFilters.semester
                    ? shownSearch() + ' · ' + total + ' save(s)' + (limited ? ' (too many matches, not all are listed: narrow the search)' : '')
                    : '');
            },
        },
    });

    // Remember whether the server cut the list short
    $(document).on('xhr.dt', '#gradeEncodingLogsTable', function (e, settings, json) {
        limited = Boolean(json && json.limited);
    });

    $(document).on('submit', '#gradeEncodingLogsSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });
})();
