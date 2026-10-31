/**
 * Kiosk > Kiosk User (resources/views/pages/employee/kiosk/users/index.blade.php). Server: KioskUserController.
 * Table, form saving and wire:navigate handling come from public/js/employee/crud.js; the table stays empty until
 * a search (last name, first name or Student ID), so thousands of accounts are never loaded. Passwords look like the
 * old system's (4 digits + K, U or G); only their hash is saved, so the password field always starts empty.
 */
(function () {
    'use strict';

    const page = () => document.getElementById('kioskUsers');
    const form = () => document.getElementById('kioskUsersSearchForm');

    const ACTIONS = CissCrud.actionsMenu([
        { action: 'reset', icon: 'fas fa-key', label: 'Reset Password' },
        { action: 'destroy', icon: 'fas fa-trash', label: 'Delete', color: '#dc3545' },
    ]);

    // 4 random digits + K, U or G (crypto random)
    function generatePassword() {
        const random = new Uint32Array(2);
        crypto.getRandomValues(random);
        return String(random[0] % 10000).padStart(4, '0') + ['K', 'U', 'G'][random[1] % 3];
    }

    CissCrud.register({
        pageId: 'kioskUsers',
        table: '#kioskUsersTable',
        order: [[1, 'asc']],
        // The search form's campus and query; the server lists nothing until a query of 2+ characters
        ajaxData: () => {
            const elements = form().elements;
            return { campus: elements.campus.value, query: elements.query.value.trim() };
        },
        columns: [
            { data: 'studid', render: $.fn.dataTable.render.text() },
            { data: 'name', render: $.fn.dataTable.render.text() },
            { data: 'resets', render: (resets, type) => type === 'display' ? resets + (resets === 1 ? ' time' : ' times') : resets },
            {
                data: 'changed_at',
                render: (timestamp, type, row) => type === 'display' || type === 'filter' ? CissCrud.escapeHtml(row.changed_at_label || '') : timestamp,
            },
            { data: null, orderable: false, searchable: false, render: () => ACTIONS },
        ],
        tableOptions: {
            language: { emptyTable: 'No kiosk accounts to show. Enter a last name, first name or Student ID No., then click Search.' },
            // Note when the search matched more accounts than the table shows
            drawCallback: function () {
                const json = this.api().ajax.json() || {};
                $('#kioskUsers .js-limit-note').text(json.limited ? 'Showing the first ' + json.data.length + ' matches. Search more specifically.' : '');
            },
        },
        actions: {
            reset(account) {
                const { $modal } = CissCrud.openFor('#resetKioskUserModal', account);
                $modal.find('.js-account-student').val(account.studid + ' - ' + account.name);
                $modal.modal('show');
            },

            destroy(account) {
                Swal.fire({
                    title: 'Delete this kiosk account?',
                    text: account.studid + ' - ' + account.name + ' will no longer be able to log in to the kiosk.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => {
                    if (result.isConfirmed) CissCrud.send('destroyUrl', account.id, 'DELETE');
                });
            },
        },
    });

    $(document).on('submit', '#kioskUsersSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    // After Add New, search the added Student ID so the new account shows in the table (crud.js reloads it on save)
    $(document).on('submit', '#addKioskUserForm', function () {
        const studentId = this.elements.studid.value.trim().toUpperCase();
        if (/^\d{4}-\d{4}-[A-Z]$/.test(studentId)) form().elements.query.value = studentId;
    });

    // Add New starts empty
    $(document).on('show.bs.modal', '#addKioskUserModal', function (e) {
        if (e.target !== this) return;
        CissCrud.openFor('#addKioskUserModal');
        $(this).find('.js-student-name').val('');
        $(this).find('.js-student-note').text('').removeClass('text-danger text-warning');
    });

    $(document).on('click', '#kioskUsers .js-generate-password', function () {
        $(this).closest('.kiosk-password-field').find('.js-password').val(generatePassword()).trigger('focus');
    });

    // The student's name once a whole Student ID is typed (2024-0001-K)
    $(document).on('input', '#kioskUsers .js-kiosk-student-id', function () {
        const $modal = $(this).closest('.modal');
        const $name = $modal.find('.js-student-name').val('');
        const $note = $modal.find('.js-student-note').text('').removeClass('text-danger text-warning');
        const studentId = this.value.trim().toUpperCase();
        if (!/^\d{4}-\d{4}-[A-Z]$/.test(studentId)) return;

        $.get(page().dataset.studentUrl.replace('__ID__', encodeURIComponent(studentId)))
            .done(function (student) {
                if (studentId !== $modal.find('.js-kiosk-student-id').val().trim().toUpperCase()) return; // typed on meanwhile
                $name.val(student.name);
                if (student.hasAccount) $note.addClass('text-warning').text('This student already has a kiosk account. Use Reset Password instead.');
            })
            .fail(function (xhr) {
                const json = xhr.responseJSON || {};
                $note.addClass('text-danger').text(json.errors ? Object.values(json.errors)[0][0] : 'Student not found.');
            });
    });
})();
