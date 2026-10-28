/**
 * Settings > A.Y. & Semester (resources/views/pages/employee/setting/aysem/semlist.blade.php).
 * Table, form saving and wire:navigate handling come from public/js/employee/crud.js.
 */
(function () {
    'use strict';

    // Badge colour per set_status (1 Inactive, 2 Active, 3 Upcoming, 4 Previous)
    const STATUS_BADGES = { 1: 'bg-danger', 2: 'bg-success', 3: 'bg-info', 4: 'bg-warning' };

    const ACTIONS = CissCrud.actionsMenu([
        { action: 'edit', icon: 'fas fa-pen', label: 'Edit' },
    ]);

    CissCrud.register({
        pageId: 'academic-terms',
        table: '#setconftable',
        order: [[1, 'desc']],
        columns: [
            { data: 'id' },
            { data: 'schlyear', render: $.fn.dataTable.render.text() },
            { data: 'semester_name', render: $.fn.dataTable.render.text() },
            { data: 'set_status', render: (status, type, row) => type === 'display' ? CissCrud.badge(STATUS_BADGES[status] || 'bg-light', row.status_name) : row.status_name },
            { data: null, orderable: false, searchable: false, render: () => ACTIONS },
        ],
        actions: {
            edit(term) {
                const { $modal, form } = CissCrud.openFor('#editTermModal', term);
                CissCrud.fill(form, term, ['schlyear', 'semester', 'set_status']);
                $modal.modal('show');
            },
        },
    });
})();
