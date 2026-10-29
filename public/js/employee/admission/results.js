/**
 * Admission > Examination Results (resources/views/pages/employee/admission/result/list.blade.php).
 * Shared admission actions come from admission/common.js (AdmissionUi); table, modal-form saving
 * and wire:navigate handling from public/js/employee/crud.js (CissCrud).
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();

    // Push is only clickable for examinees who passed or are a Second Qualifier (checked again on the server)
    function actionsFor(row) {
        return CissCrud.actionsMenu([
            { action: 'view', icon: 'fas fa-eye', label: 'View Data' },
            { action: 'preEnrollment', icon: 'fas fa-file-pdf', label: 'Generate Pre-Enrollment' },
            { action: 'result', icon: 'fas fa-file-lines', label: 'Update Test Result' },
            { action: 'push', icon: 'fas fa-check', label: 'Push to Confirm', color: 'green', disabled: !row.can_proceed },
        ]);
    }

    function remarkBadge(remark, row) {
        const passed = remark && remark !== 'Failed';
        let html = CissCrud.badge(passed ? 'bg-success' : 'bg-danger', remark || 'None');
        if (String(row.qualifier) === '2') html += ' ' + CissCrud.badge('bg-warning text-dark', '2nd Qualifier');
        return html;
    }

    CissCrud.register({
        pageId: 'results',
        table: '#exresultlistTable',
        order: [[0, 'asc']],
        ajaxData: AdmissionUi.searchFilters,
        columns: [
            { data: 'admission_id', render: text },
            { data: 'display_name', render: text },
            { data: 'type_name', render: text },
            { data: 'raw_score', render: text },
            { data: 'remark', render: (remark, type, row) => type === 'display' ? remarkBadge(remark, row) : (remark || '') },
            { data: 'schedule_at', render: (at, type, row) => type === 'display' ? CissCrud.escapeHtml(row.schedule_label || '') : at },
            { data: 'campus', render: text },
            { data: 'strand', render: text },
            { data: null, orderable: false, searchable: false, render: (data, type, row) => row.can_manage ? actionsFor(row) : '' },
        ],
        actions: Object.assign({}, AdmissionUi.actions, {
            push(examinee) {
                AdmissionUi.confirmThen({
                    title: 'Push to Confirm List?',
                    html: CissCrud.escapeHtml(examinee.display_name) + ' will be moved to the Confirm list for the department interview.',
                    confirmButtonText: 'Yes, push',
                }, () => CissCrud.send('pushUrl', examinee.id));
            },
        }),
    });
})();
