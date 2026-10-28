/**
 * Settings > User's Management (resources/views/pages/employee/setting/user/listusers.blade.php).
 *
 * Pushed to <head>, so with wire:navigate it loads once and stays loaded.
 * Delegated handlers (clicks, form submits) are bound once below; the DataTable is
 * built on every livewire:navigated that lands on this page and destroyed when leaving.
 */
(function () {
    'use strict';

    // Badge colour per role id (same colours as before)
    const ROLE_BADGES = {
        0: 'bg-secondary', 1: 'bg-primary', 2: 'bg-success', 3: 'bg-danger', 4: 'bg-warning',
        5: 'bg-info', 6: 'bg-info', 7: 'bg-info', 8: 'bg-warning', 9: 'bg-warning',
        10: 'bg-warning', 11: 'bg-warning', 12: 'bg-secondary', 13: 'bg-secondary', 14: 'bg-secondary',
        15: 'bg-warning', 16: 'bg-ossa', 17: 'bg-info', 18: 'bg-info', 19: 'bg-secondary',
        20: 'bg-secondary', 21: 'bg-secondary',
    };

    const ACTIONS_MENU =
        '<div class="d-inline-block">' +
            '<a class="btn btn-success btn-sm dropdown-toggle dropdown-icon text-light" data-bs-toggle="dropdown"></a>' +
            '<div class="dropdown-menu">' +
                '<a href="#" class="dropdown-item" data-action="edit"><i class="fas fa-pen"></i> Edit Info</a>' +
                '<a href="#" class="dropdown-item" data-action="password"><i class="fas fa-lock"></i> Edit Pass</a>' +
                '<a href="#" class="dropdown-item" data-action="menuAccess"><i class="fas fa-keyboard" style="color: green"></i> User Access</a>' +
                '<a href="#" class="dropdown-item" data-action="yearAccess"><i class="fas fa-laptop-code" style="color: purple"></i> User Allow</a>' +
                '<a href="#" class="dropdown-item" data-action="status"><i class="fas fa-toggle-off" style="color: red"></i> Account Status</a>' +
            '</div>' +
        '</div>';

    let table = null;
    let urls = {};

    toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right' };

    document.addEventListener('livewire:navigated', init);
    document.addEventListener('livewire:navigating', function () {
        if (table) {
            table.destroy();
            table = null;
        }
    });

    function init() {
        const page = document.getElementById('user-management');
        if (!page) return;

        urls = page.dataset;
        table = $('#userlist').DataTable({
            ajax: { url: urls.listUrl },
            responsive: true,
            columns: [
                { data: 'id' },
                { data: 'display_name', render: $.fn.dataTable.render.text() },
                { data: 'email', render: $.fn.dataTable.render.text() },
                { data: 'role', render: (role, type, row) => type === 'display' ? badge(ROLE_BADGES[role] || 'bg-light', row.role_name) : row.role_name },
                { data: 'campus', render: $.fn.dataTable.render.text() },
                { data: 'is_active', render: (active, type) => type === 'display' ? badge(active ? 'bg-success' : 'bg-danger', active ? 'Enabled' : 'Disabled') : (active ? 'Enabled' : 'Disabled') },
                { data: null, orderable: false, searchable: false, render: () => ACTIONS_MENU },
            ],
            createdRow: function (row, data) {
                row.id = 'tr-' + data.id;
            },
        });
    }

    // ---------- Helpers ----------
    function escapeHtml(text) {
        return $('<div>').text(text == null ? '' : String(text)).html();
    }

    function badge(className, text) {
        return '<span class="badge ' + className + '">' + escapeHtml(text) + '</span>';
    }

    function urlFor(key, userId) {
        return urls[key].replace('__ID__', encodeURIComponent(userId || ''));
    }

    // Row data for a clicked element (handles DataTables responsive child rows)
    function rowData(element) {
        let $tr = $(element).closest('tr');
        if ($tr.hasClass('child')) $tr = $tr.prev();
        return table.row($tr).data();
    }

    function errorMessage(xhr) {
        const json = xhr.responseJSON || {};
        if (json.errors) return Object.values(json.errors)[0][0];
        return json.message || 'An error occurred. Please try again.';
    }

    // Prepare a modal's form for a user: remember the id and fill the read-only name/role
    function openFor(modalId, user) {
        const $modal = $(modalId);
        const form = $modal.find('form')[0];

        form.reset();
        form.dataset.userId = user.id;
        $modal.find('.js-user-name').val(user.display_name);
        $modal.find('.js-user-role').val(user.role);

        return { $modal, form };
    }

    function checkValues($modal, name, values) {
        $modal.find('input[name="' + name + '[]"]').each(function () {
            this.checked = (values || []).includes(this.value);
        });
    }

    // ---------- Row actions ----------
    const actions = {
        edit(user) {
            const { $modal, form } = openFor('#editUserModal', user);
            ['fname', 'mname', 'lname', 'ext', 'email', 'campus', 'dept', 'role'].forEach(function (field) {
                form.elements[field].value = user[field] ?? '';
            });
            $modal.modal('show');
        },

        password(user) {
            openFor('#userPasswordModal', user).$modal.modal('show');
        },

        menuAccess(user) {
            const { $modal } = openFor('#userMenuAccessModal', user);
            $.get(urlFor('menuAccessUrl', user.id))
                .done(function (response) {
                    checkValues($modal, 'buttons', response.buttons);
                    $modal.modal('show');
                })
                .fail((xhr) => toastr.error(errorMessage(xhr)));
        },

        yearAccess(user) {
            const { $modal } = openFor('#userYearAccessModal', user);
            $.get(urlFor('yearAccessUrl', user.id))
                .done(function (response) {
                    checkValues($modal, 'schlyraccess', response.schlyraccess);
                    $modal.modal('show');
                })
                .fail((xhr) => toastr.error(errorMessage(xhr)));
        },

        status(user) {
            const { $modal, form } = openFor('#userStatusModal', user);
            form.elements.statuser.value = user.is_active ? '1' : '2';
            $modal.modal('show');
        },
    };

    $(document).on('click', '#userlist [data-action]', function (e) {
        e.preventDefault();
        const user = rowData(this);
        if (user) actions[this.dataset.action](user);
    });

    // ---------- Form submits (every modal form) ----------
    $(document).on('submit', '#user-management form[data-url]', function (e) {
        e.preventDefault();

        const form = this;
        const $submit = $(form).find('[type="submit"]').prop('disabled', true);
        const data = $(form).serializeArray();

        // Laravel reads the real method (PUT) from _method; unchecked checkbox lists are sent empty
        data.push({ name: '_method', value: form.dataset.method });
        $(form).find('input[type="checkbox"][name$="[]"]').first().each(function () {
            if (!$(form).find('input[name="' + this.name + '"]:checked').length) {
                data.push({ name: this.name.replace('[]', ''), value: '' });
            }
        });

        $.ajax({
            url: urlFor(camelCase(form.dataset.url), form.dataset.userId),
            type: 'POST',
            data: $.param(data),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        })
            .done(function (response) {
                toastr.success(response.message);
                $(form).closest('.modal').modal('hide');
                form.reset();
                if (table) table.ajax.reload(null, false); // keep the current page
            })
            .fail((xhr) => toastr.error(errorMessage(xhr)))
            .always(() => $submit.prop('disabled', false));
    });

    // "update-url" (Blade attribute) => "updateUrl" (dataset key)
    function camelCase(key) {
        return key.replace(/-([a-z])/g, (m, letter) => letter.toUpperCase());
    }

    // Capitalize the first letter of every word in name fields
    $(document).on('input', '#user-management .js-capitalize', function () {
        this.value = this.value.replace(/(^|\s)(\S)/g, (m, space, letter) => space + letter.toUpperCase());
    });
})();
