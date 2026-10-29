/**
 * Enrollment > Enrollment History (resources/views/pages/employee/enrollment/history/index.blade.php).
 * Server: EnrollmentHistoryController. The results table and wire:navigate handling come from CissCrud
 * (public/js/employee/crud.js); searching reloads the table without a page reload.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const query = () => {
        const form = document.getElementById('historySearchForm');
        return form ? form.elements.query.value.trim() : '';
    };

    CissCrud.register({
        pageId: 'enrollmentHistory',
        table: '#studhisTable',
        order: [[1, 'asc']],
        ajaxData: () => ({ query: query() }),
        columns: [
            { data: 'stud_id', render: text },
            { data: 'lname', render: text },
            { data: 'fname', render: text },
            { data: 'mname', render: text },
            { data: 'ext', render: (ext) => CissCrud.escapeHtml(ext && ext !== 'N/A' ? ext : '') },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => CissCrud.actionsMenu([
                    { action: 'history', icon: 'fas fa-eye', label: 'Enrollment History' },
                    { action: 'preEnrollment', icon: 'fas fa-file-pdf', label: 'Pre-Enrollment Form', color: '#dc3545' },
                ]),
            },
        ],
        tableOptions: {
            language: { emptyTable: 'No students to show. Enter a last name or Student ID No., then click OK.' },
            lengthChange: false,
            // Note when the search matched more students than the table shows
            drawCallback: function () {
                const json = this.api().ajax.json() || {};
                $('#enrollmentHistory .js-limit-note').text(json.limited ? 'Showing the first ' + json.data.length + ' matches. Search more specifically.' : '');
            },
        },
        actions: {
            // The student's enrollments (all terms) in a modal
            history(row) {
                const $modal = $('#viewStudHisModal');

                $.get(row.history_url)
                    .done(function (response) {
                        $modal.find('.js-student-name').text(response.name);
                        $modal.find('.js-history-rows').html(response.data.length
                            ? response.data.map((history) => '<tr>' +
                                '<td>' + CissCrud.escapeHtml(history.studentID) + '</td>' +
                                '<td>' + CissCrud.escapeHtml(history.schlyear) + '</td>' +
                                '<td>' + CissCrud.badge(history.semester === 3 ? 'bg-secondary' : 'bg-info', history.semester_name) + '</td>' +
                                '<td>' + CissCrud.escapeHtml(history.course || '') + '</td>' +
                                '<td>' + CissCrud.escapeHtml(history.studYear) + '</td>' +
                                '<td>' + CissCrud.escapeHtml(history.studSec) + '</td>' +
                                '<td>' + CissCrud.escapeHtml(history.campus) + '</td>' +
                                '</tr>').join('')
                            : '<tr><td colspan="7" class="text-center text-muted">No enrollment history found.</td></tr>');
                        $modal.modal('show');
                    })
                    .fail(() => toastr.error('Could not load the enrollment history.'));
            },

            // Pre-Enrollment Form PDF in the modal's iframe
            preEnrollment(row) {
                CissCrud.openPdf('#preEnrollmentFormModal', row.lname + ', ' + row.fname, row.pdf_url);
            },
        },
    });

    $(document).on('submit', '#historySearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });
})();
