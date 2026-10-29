/**
 * Shared behaviour for employee "table + modals" pages (DataTable fed by AJAX, modal forms saved by AJAX).
 * Loaded once from employee-assets; each page script only calls CissCrud.register({...}).
 *
 * Page markup contract:
 *   <div id="{pageId}" data-list-url="..." data-store-url="..." data-update-url=".../__ID__">  (the URLs)
 *     <table id="...">                                    the DataTable
 *     [data-action="edit"] inside rows                    calls config.actions.edit(row)
 *     <form data-url="update-url" data-method="PUT">      saved by AJAX (x-employee.form-modal)
 *
 * With wire:navigate this file stays loaded: the table is built on every livewire:navigated that
 * lands on a registered page and destroyed when leaving; the delegated handlers are bound once.
 */
window.CissCrud = (function () {
    'use strict';

    const pages = {};
    let current = null; // { config, table, urls }

    toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right' };

    document.addEventListener('livewire:navigated', function () {
        Object.values(pages).forEach(function (config) {
            const page = document.getElementById(config.pageId);
            if (page) start(config, page);
        });
    });

    document.addEventListener('livewire:navigating', function () {
        if (current) {
            current.table.destroy();
            current = null;
        }
    });

    function register(config) {
        pages[config.pageId] = config;
    }

    function start(config, page) {
        const urls = page.dataset;

        const table = $(config.table).DataTable({
            ajax: {
                url: urls.listUrl,
                // Optional extra query values (e.g. search filters), read on every (re)load
                data: config.ajaxData ? (params) => Object.assign(params, config.ajaxData()) : undefined,
            },
            responsive: true,
            order: config.order || [[0, 'asc']],
            columns: config.columns,
            createdRow: function (row, data) {
                row.id = 'tr-' + data.id;
            },
        });

        current = { config, table, urls };
    }

    // ---------- Helpers for page scripts ----------
    function escapeHtml(text) {
        return $('<div>').text(text == null ? '' : String(text)).html();
    }

    function badge(className, text) {
        return '<span class="badge ' + className + '">' + escapeHtml(text) + '</span>';
    }

    // items: [{ action, icon, label, color?, disabled? }] (disabled items are shown greyed out, not clickable)
    function actionsMenu(items) {
        return '<div class="d-inline-block">' +
            '<a class="btn btn-success btn-sm dropdown-toggle dropdown-icon text-light" data-bs-toggle="dropdown"></a>' +
            '<div class="dropdown-menu">' +
            items.map(function (item) {
                const style = item.color && !item.disabled ? ' style="color: ' + item.color + '"' : '';
                const icon = '<i class="' + item.icon + '"' + style + '></i> ' + item.label;
                return item.disabled
                    ? '<span class="dropdown-item disabled">' + icon + '</span>'
                    : '<a href="#" class="dropdown-item" data-action="' + item.action + '">' + icon + '</a>';
            }).join('') +
            '</div></div>';
    }

    function urlFor(key, id) {
        return current.urls[key].replace('__ID__', encodeURIComponent(id == null ? '' : id));
    }

    function errorMessage(xhr) {
        const json = xhr.responseJSON || {};
        if (json.errors) return Object.values(json.errors)[0][0];
        return json.message || 'An error occurred. Please try again.';
    }

    // Reset a modal's form and remember which record it edits
    function openFor(modalId, row) {
        const $modal = $(modalId);
        const form = $modal.find('form')[0];

        form.reset();
        form.dataset.recordId = row ? row.id : '';

        return { $modal, form };
    }

    // Set form fields by their name attribute. (form.elements[x] also matches ids, so a field named "region"
    // next to a select with id="region" would return both and the value would silently not be set.)
    function fill(form, row, fields) {
        fields.forEach(function (field) {
            const input = form.querySelector('[name="' + field + '"]');
            if (input) input.value = row[field] ?? '';
        });
    }

    // GET a URL key for a record; shows an error toast on failure
    function fetchFor(key, id) {
        return $.get(urlFor(key, id)).fail(function (xhr) {
            toastr.error(errorMessage(xhr));
        });
    }

    // Reload the table (e.g. after changing search filters)
    function reload() {
        if (current) current.table.ajax.reload();
    }

    // One-click action without a form (e.g. after a confirm popup): PUT/DELETE a URL key, then refresh the table
    function send(key, id, method) {
        return $.ajax({
            url: urlFor(key, id),
            type: 'POST',
            data: { _method: method || 'PUT' },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        })
            .done(function (response) {
                toastr.success(response.message);
                current.table.ajax.reload(null, false);
            })
            .fail((xhr) => toastr.error(errorMessage(xhr)));
    }

    // ---------- Delegated handlers (bound once) ----------

    // Row actions: [data-action] inside the current page's table
    $(document).on('click', 'table [data-action]', function (e) {
        if (!current) return;
        e.preventDefault();

        let $tr = $(this).closest('tr');
        if ($tr.hasClass('child')) $tr = $tr.prev(); // responsive child row

        const row = current.table.row($tr).data();
        const action = current.config.actions[this.dataset.action];
        if (row && action) action(row);
    });

    // Modal forms: <form data-url="store-url" data-method="POST|PUT">
    $(document).on('submit', 'form[data-url]', function (e) {
        if (!current) return;
        e.preventDefault();

        const form = this;
        const $submit = $(form).find('[type="submit"]').prop('disabled', true);
        const data = $(form).serializeArray();

        // Laravel reads the real method (PUT) from _method; an unchecked checkbox list is sent empty
        data.push({ name: '_method', value: form.dataset.method || 'POST' });
        $(form).find('input[type="checkbox"][name$="[]"]').first().each(function () {
            if (!$(form).find('input[name="' + this.name + '"]:checked').length) {
                data.push({ name: this.name.replace('[]', ''), value: '' });
            }
        });

        const urlKey = form.dataset.url.replace(/-([a-z])/g, (m, letter) => letter.toUpperCase()); // "update-url" => "updateUrl"

        $.ajax({
            url: urlFor(urlKey, form.dataset.recordId),
            type: 'POST',
            data: $.param(data),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        })
            .done(function (response) {
                toastr.success(response.message);
                $(form).closest('.modal').modal('hide');
                form.reset();
                current.table.ajax.reload(null, false); // keep the current page
            })
            .fail((xhr) => toastr.error(errorMessage(xhr)))
            .always(() => $submit.prop('disabled', false));
    });

    // Capitalize the first letter of every word (class js-capitalize)
    $(document).on('input', '.js-capitalize', function () {
        this.value = this.value.replace(/(^|\s)(\S)/g, (m, space, letter) => space + letter.toUpperCase());
    });

    return { register, escapeHtml, badge, actionsMenu, openFor, fill, fetchFor, reload, send };
})();
