/**
 * Queue > Counters (resources/views/pages/employee/queue/counters/index.blade.php). Server: QueueCounterController.
 * Table, modal forms and wire:navigate handling come from public/js/employee/crud.js. The personnel options of the
 * modals come with the table data (Registrar staff of the campus).
 */
(function () {
    'use strict';

    const STATUS_BADGES = { 1: 'bg-secondary', 2: 'bg-success', 3: 'bg-warning text-dark' };

    const ACTIONS = CissCrud.actionsMenu([
        { action: 'edit', icon: 'fas fa-pen', label: 'Edit' },
        { action: 'open', icon: 'fas fa-door-open', label: 'Open', color: '#198754' },
        { action: 'hold', icon: 'fas fa-pause', label: 'Hold', color: '#b58105' },
        { action: 'close', icon: 'fas fa-door-closed', label: 'Close' },
        { action: 'destroy', icon: 'fas fa-trash', label: 'Delete', color: '#dc3545' },
    ]);

    function setStatus(counter, status) {
        $.ajax({
            url: CissCrud.urlFor('statusUrl', counter.id),
            type: 'POST',
            data: { _method: 'PUT', counterstatus: status },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        })
            .done((response) => { toastr.success(response.message); CissCrud.reload(); })
            .fail((xhr) => toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Could not change the status.'));
    }

    CissCrud.register({
        pageId: 'queueCounters',
        table: '#queueCountersTable',
        order: [[0, 'asc']],
        columns: [
            { data: 'windowname', render: (window, type) => type === 'display' ? '<strong>Window ' + CissCrud.escapeHtml(window) + '</strong>' : window },
            { data: 'category', render: $.fn.dataTable.render.text(), defaultContent: '' },
            { data: 'staff', render: (staff) => staff ? CissCrud.escapeHtml(staff) : '<span class="text-muted">Unassigned</span>' },
            { data: 'status_label', render: (label, type, row) => type === 'display' ? CissCrud.badge(STATUS_BADGES[row.counterstatus] || 'bg-secondary', label) : label },
            { data: null, orderable: false, searchable: false, render: () => ACTIONS },
        ],
        tableOptions: {
            language: { emptyTable: 'No counters in this campus yet. Click Add Counter.' },
            paging: false,
            // Personnel of the campus for the modals' selects
            drawCallback: function () {
                const json = this.api().ajax.json() || {};
                if (!json.personnel) return;
                const options = '<option value="" selected disabled>Select</option>' +
                    json.personnel.map((person) => '<option value="' + person.id + '">' + CissCrud.escapeHtml(person.name) + '</option>').join('');
                $('#queueCounters .js-personnel').html(options);
            },
        },
        actions: {
            edit(counter) {
                const { $modal, form } = CissCrud.openFor('#editCounterModal', counter);
                $modal.find('.js-window').val('Window ' + counter.windowname);
                CissCrud.fill(form, counter, ['useridlog', 'category']);
                $modal.modal('show');
            },
            open: (counter) => setStatus(counter, 2),
            hold: (counter) => setStatus(counter, 3),
            close: (counter) => setStatus(counter, 1),
            destroy(counter) {
                Swal.fire({
                    title: 'Delete Window ' + counter.windowname + '?',
                    text: counter.staff ? counter.staff + ' will no longer have a queue counter.' : '',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => {
                    if (result.isConfirmed) CissCrud.send('destroyUrl', counter.id, 'DELETE');
                });
            },
        },
    });

    // Add Counter starts empty
    $(document).on('show.bs.modal', '#addCounterModal', function (e) {
        if (e.target === this) CissCrud.openFor('#addCounterModal');
    });
})();
