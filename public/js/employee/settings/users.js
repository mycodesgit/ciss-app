/**
 * Settings > User's Management (resources/views/pages/employee/setting/user/listusers.blade.php).
 * Table, form saving and wire:navigate handling come from public/js/employee/crud.js.
 */
(function () {
    'use strict';

    // Badge colour per role id
    const ROLE_BADGES = {
        0: 'bg-secondary', 1: 'bg-primary', 2: 'bg-success', 3: 'bg-danger', 4: 'bg-warning',
        5: 'bg-info', 6: 'bg-info', 7: 'bg-info', 8: 'bg-warning', 9: 'bg-warning',
        10: 'bg-warning', 11: 'bg-warning', 12: 'bg-secondary', 13: 'bg-secondary', 14: 'bg-secondary',
        15: 'bg-warning', 16: 'bg-ossa', 17: 'bg-info', 18: 'bg-info', 19: 'bg-secondary',
        20: 'bg-secondary', 21: 'bg-secondary',
    };

    const ACTIONS = CissCrud.actionsMenu([
        { action: 'edit', icon: 'fas fa-pen', label: 'Edit Info' },
        { action: 'password', icon: 'fas fa-lock', label: 'Edit Pass' },
        { action: 'menuAccess', icon: 'fas fa-keyboard', label: 'User Access', color: 'green' },
        { action: 'yearAccess', icon: 'fas fa-laptop-code', label: 'User Allow', color: 'purple' },
        { action: 'status', icon: 'fas fa-toggle-off', label: 'Account Status', color: 'red' },
    ]);

    // Opens a user modal and fills its read-only name / user level
    function openUserModal(modalId, user) {
        const opened = CissCrud.openFor(modalId, user);
        opened.$modal.find('.js-user-name').val(user.display_name);
        opened.$modal.find('.js-user-role').val(user.role);
        return opened;
    }

    function checkValues($modal, name, values) {
        $modal.find('input[name="' + name + '[]"]').each(function () {
            this.checked = (values || []).includes(this.value);
        });
    }

    CissCrud.register({
        pageId: 'user-management',
        table: '#userlist',
        order: [[1, 'asc']],
        columns: [
            { data: 'id' },
            { data: 'display_name', render: $.fn.dataTable.render.text() },
            { data: 'email', render: $.fn.dataTable.render.text() },
            { data: 'role', render: (role, type, row) => type === 'display' ? CissCrud.badge(ROLE_BADGES[role] || 'bg-light', row.role_name) : row.role_name },
            { data: 'campus', render: $.fn.dataTable.render.text() },
            { data: 'is_active', render: (active, type) => type === 'display' ? CissCrud.badge(active ? 'bg-success' : 'bg-danger', active ? 'Enabled' : 'Disabled') : (active ? 'Enabled' : 'Disabled') },
            { data: null, orderable: false, searchable: false, render: () => ACTIONS },
        ],
        actions: {
            edit(user) {
                const { $modal, form } = openUserModal('#editUserModal', user);
                CissCrud.fill(form, user, ['fname', 'mname', 'lname', 'ext', 'email', 'campus', 'dept', 'role']);
                $modal.modal('show');
            },

            password(user) {
                openUserModal('#userPasswordModal', user).$modal.modal('show');
            },

            menuAccess(user) {
                const { $modal } = openUserModal('#userMenuAccessModal', user);
                CissCrud.fetchFor('menuAccessUrl', user.id).done(function (response) {
                    checkValues($modal, 'buttons', response.buttons);
                    $modal.modal('show');
                });
            },

            yearAccess(user) {
                const { $modal } = openUserModal('#userYearAccessModal', user);
                CissCrud.fetchFor('yearAccessUrl', user.id).done(function (response) {
                    checkValues($modal, 'schlyraccess', response.schlyraccess);
                    $modal.modal('show');
                });
            },

            status(user) {
                const { $modal, form } = openUserModal('#userStatusModal', user);
                form.elements.statuser.value = user.is_active ? '1' : '2';
                $modal.modal('show');
            },
        },
    });
})();
