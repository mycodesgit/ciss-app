/**
 * Admission > Accepted Applicants (resources/views/pages/employee/admission/accepted/list.blade.php).
 * Shared admission actions come from admission/common.js (AdmissionUi); table and wire:navigate
 * handling from public/js/employee/crud.js (CissCrud).
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();

    // Push Enrollment only for applicants not pushed yet (the server checks this too)
    function actionsFor(row) {
        return CissCrud.actionsMenu([
            { action: 'preEnrollment', icon: 'fas fa-file-pdf', label: 'Generate Pre-Enrollment' },
            row.pushed
                ? { icon: 'fas fa-check', label: 'Done Pushed Enrollment', disabled: true }
                : { action: 'push', icon: 'fas fa-check', label: 'Push Enrollment', color: 'green' },
        ]);
    }

    CissCrud.register({
        pageId: 'accepted',
        table: '#acceptedTableapp',
        order: [[0, 'asc']],
        ajaxData: AdmissionUi.searchFilters,
        columns: [
            { data: 'admission_id', render: text },
            { data: 'display_name', render: text },
            { data: 'type_name', render: text },
            { data: 'interviewer', render: text },
            { data: 'accepted_course', render: text },
            { data: 'stud_id', render: (id, type) => type === 'display' ? (id ? CissCrud.escapeHtml(id) : '<span class="text-muted">None</span>') : (id || '') },
            {
                data: 'pushed',
                render: (pushed, type) => {
                    const label = pushed ? 'Pushed to Enrollment' : 'Not Pushed to Enrollment';
                    return type === 'display' ? CissCrud.badge(pushed ? 'bg-success' : 'bg-warning', label) : label;
                },
            },
            { data: 'campus', render: text },
            { data: 'strand', render: text },
            { data: null, orderable: false, searchable: false, render: (data, type, row) => row.can_manage ? actionsFor(row) : '' },
        ],
        actions: Object.assign({}, AdmissionUi.actions, {
            push(applicant) {
                AdmissionUi.confirmThen({
                    title: 'Push to Enrollment?',
                    html: 'A student record and Student ID will be created for ' + CissCrud.escapeHtml(applicant.display_name) + '.',
                    confirmButtonText: 'Yes, push',
                }, () => CissCrud.send('pushUrl', applicant.id));
            },
        }),
    });
})();
