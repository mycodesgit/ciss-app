/**
 * Settings > Grades Password (resources/views/pages/employee/setting/gradepass/gpass.blade.php).
 * Table, form saving and wire:navigate handling come from public/js/employee/crud.js.
 */
(function () {
    'use strict';

    const ACTIONS = CissCrud.actionsMenu([
        { action: 'edit', icon: 'ti ti-settings-cog', label: 'Edit Password' },
    ]);

    // Show the formatted date, but sort/search by the timestamp
    function dateColumn(field) {
        return {
            data: field,
            render: (timestamp, type, row) => type === 'display' || type === 'filter' ? CissCrud.escapeHtml(row[field + '_label'] || '') : timestamp,
        };
    }

    CissCrud.register({
        pageId: 'grade-passwords',
        table: '#setgradepasstable',
        order: [[2, 'asc']],
        columns: [
            { data: null, orderable: false, searchable: false, render: (data, type, row, meta) => meta.row + 1 },
            // The password is never shown; an old plain-text one should be changed so it is saved protected
            {
                data: 'protected',
                render: (isProtected, type) => {
                    const label = isProtected ? 'Protected' : 'Plain text: change it';
                    return type === 'display'
                        ? '<i class="ti ti-lock"></i> •••••••• ' + CissCrud.badge(isProtected ? 'bg-success' : 'bg-warning text-dark', label)
                        : label;
                },
            },
            { data: 'campus_name', render: $.fn.dataTable.render.text() },
            dateColumn('created_at'),
            dateColumn('updated_at'),
            { data: null, orderable: false, searchable: false, render: () => ACTIONS },
        ],
        actions: {
            edit(pass) {
                const { $modal, form } = CissCrud.openFor('#editGradePassModal', pass);
                $modal.find('.js-campus-name').val(pass.campus_name);
                $modal.modal('show'); // the new password field starts empty (the saved one can't be read)
            },
        },
    });
})();
