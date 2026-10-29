/**
 * Admission > Configure Admission (resources/views/pages/employee/admission/configure/admsnconfig.blade.php).
 * Every tab is a "section" with its own table, add form and edit modal:
 *   <div class="tab-pane" data-section="programs" data-list-url data-store-url data-record-url=".../__ID__">
 *     <form data-store>               the add form, POSTed to store-url
 *     <table>                         the DataTable (rows from list-url)
 *   <form data-edit="programs">       the edit modal's form, PUT to record-url (Delete sends DELETE there)
 *
 * Pushed to <head>, so with wire:navigate it loads once: the tables are built on every livewire:navigated
 * that lands on the page and destroyed when leaving; the delegated handlers are bound once.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const labelOf = (key) => (value, type, row) => type === 'display' ? CissCrud.escapeHtml(row[key]) : value;

    const sections = {
        programs: {
            modal: '#editProgramModal',
            fields: ['code', 'program'],
            columns: [{ data: 'code', render: text }, { data: 'program', render: text }],
        },
        strands: {
            modal: '#editStrandModal',
            fields: ['code', 'strand'],
            columns: [{ data: 'code', render: text }, { data: 'strand', render: text }, { data: 'campus_name', render: text }],
        },
        dates: {
            modal: '#editDateModal',
            fields: ['date'],
            columns: [{ data: 'campus_name', render: text }, { data: 'date', render: labelOf('date_label') }],
            onReload: loadDateOptions, // the Time Schedule selects list these dates
        },
        times: {
            modal: '#editDateTimeModal',
            fields: ['date', 'time', 'slots'],
            columns: [
                { data: 'campus_name', render: text },
                { data: 'date', render: labelOf('date_label') },
                { data: 'time', render: labelOf('time_label') },
                {
                    data: 'slots',
                    render: (slots, type, row) => type === 'display'
                        ? slots + ' <small class="text-muted">(' + row.booked + ' booked)</small>'
                        : slots,
                },
            ],
        },
        venues: {
            modal: '#editVenueModal',
            fields: ['venue'],
            columns: [{ data: 'campus_name', render: text }, { data: 'adyear', render: text }, { data: 'venue', render: text }],
        },
        years: {
            modal: '#editYearModal',
            fields: ['adyear', 'status'],
            columns: [
                { data: 'adyear', render: text },
                { data: 'status', render: (status, type) => type === 'display' ? CissCrud.badge(status === 'On' ? 'bg-success' : 'bg-secondary', status) : status },
            ],
            onReload: syncYearOptions, // the Venue tab's year select
        },
    };

    const tables = {};

    document.addEventListener('livewire:navigated', function () {
        const page = document.getElementById('admissionConfig');
        if (!page) return;

        page.querySelectorAll('.tab-pane[data-section]').forEach(start);
        loadDateOptions();
    });

    document.addEventListener('livewire:navigating', function () {
        Object.keys(tables).forEach(function (name) {
            tables[name].destroy();
            delete tables[name];
        });
    });

    function start(pane) {
        const name = pane.dataset.section;

        tables[name] = $(pane).find('table').DataTable({
            ajax: pane.dataset.listUrl,
            responsive: true,
            order: [], // keep the server's order
            columns: sections[name].columns.concat({
                data: null,
                orderable: false,
                searchable: false,
                render: (data, type, row) => actionsFor(row),
            }),
        });
    }

    function actionsFor(row) {
        return row.can_manage
            ? CissCrud.actionsMenu([
                { action: 'edit', icon: 'fas fa-pen', label: 'Edit' },
                { action: 'delete', icon: 'fas fa-trash', label: 'Delete', color: 'red' },
            ])
            : CissCrud.actionsMenu([{ icon: 'fas fa-lock', label: 'You don\'t have permission.', disabled: true }]);
    }

    function paneOf(name) {
        return document.querySelector('#admissionConfig .tab-pane[data-section="' + name + '"]');
    }

    function recordUrl(name, id) {
        return paneOf(name).dataset.recordUrl.replace('__ID__', encodeURIComponent(id));
    }

    // Reload a table (keeping its page), then refresh whatever depends on it
    function reload(name) {
        const table = tables[name];
        if (!table) return;

        table.ajax.reload(function () {
            if (sections[name].onReload) sections[name].onReload(table);
        }, false);
    }

    function errorMessage(xhr) {
        const json = xhr.responseJSON || {};
        if (json.errors) return Object.values(json.errors)[0][0];
        return json.message || 'An error occurred. Please try again.';
    }

    // POST with Laravel's _method (PUT / DELETE); shows the error toast on failure
    function request(url, method, data) {
        return $.ajax({
            url: url,
            type: 'POST',
            data: (data ? data + '&' : '') + '_method=' + method,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        }).fail((xhr) => toastr.error(errorMessage(xhr)));
    }

    function save(form, url, method, name) {
        const $submit = $(form).find('[type="submit"]').prop('disabled', true);

        request(url, method, $(form).serialize())
            .done(function (response) {
                toastr.success(response.message);
                form.reset();
                $(form).closest('.modal').modal('hide');
                reload(name);
            })
            .always(() => $submit.prop('disabled', false));
    }

    function edit(name, row) {
        const $modal = $(sections[name].modal);
        const form = $modal.find('form')[0];

        form.reset();
        form.dataset.recordId = row.id;
        CissCrud.fill(form, row, sections[name].fields);
        $modal.modal('show');
    }

    function remove(name, row) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to recover this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            request(recordUrl(name, row.id), 'DELETE').done(function (response) {
                toastr.success(response.message);
                reload(name);
            });
        });
    }

    // Date selects of the Time Schedule tab (add form and edit modal): this campus's admission dates
    function loadDateOptions() {
        const page = document.getElementById('admissionConfig');
        if (!page) return;

        $.get(page.dataset.dateOptionsUrl).done(function (dates) {
            const options = '<option value="">Select date</option>' + dates.map(function (date) {
                return '<option value="' + CissCrud.escapeHtml(date.value) + '">' + CissCrud.escapeHtml(date.label) + '</option>';
            }).join('');

            $('#admissionConfig .js-date-options').html(options);
        });
    }

    // Year select of the Venue tab, rebuilt from the Year table (the "On" year stays selected)
    function syncYearOptions(table) {
        const years = table.rows().data().toArray();

        $('#admissionConfig .js-year-options').html(years.map(function (year) {
            const selected = year.status === 'On' ? ' selected' : '';
            return '<option value="' + CissCrud.escapeHtml(year.adyear) + '"' + selected + '>' + CissCrud.escapeHtml(year.adyear) + '</option>';
        }).join(''));
    }

    // ---------- Delegated handlers (bound once) ----------

    // Row actions (Edit / Delete) of any tab's table
    $(document).on('click', '#admissionConfig .tab-pane[data-section] table [data-action]', function (e) {
        e.preventDefault();

        const name = this.closest('[data-section]').dataset.section;
        let $tr = $(this).closest('tr');
        if ($tr.hasClass('child')) $tr = $tr.prev(); // responsive child row

        const row = tables[name] && tables[name].row($tr).data();
        if (!row) return;

        if (this.dataset.action === 'edit') edit(name, row);
        if (this.dataset.action === 'delete') remove(name, row);
    });

    // Add forms (inside a tab)
    $(document).on('submit', '#admissionConfig form[data-store]', function (e) {
        e.preventDefault();

        const pane = this.closest('[data-section]');
        save(this, pane.dataset.storeUrl, 'POST', pane.dataset.section);
    });

    // Edit modals
    $(document).on('submit', '#admissionConfig form[data-edit]', function (e) {
        e.preventDefault();

        const name = this.dataset.edit;
        save(this, recordUrl(name, this.dataset.recordId), 'PUT', name);
    });

    // Tables built inside a hidden tab get their column widths when the tab is shown
    $(document).on('shown.bs.tab', '#admissionConfig [data-bs-toggle="pill"]', function () {
        Object.values(tables).forEach((table) => table.columns.adjust().responsive.recalc());
    });
})();
