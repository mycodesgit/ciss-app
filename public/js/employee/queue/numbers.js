/**
 * Queue > Numbers (resources/views/pages/employee/queue/numbers/index.blade.php). Server: QueueNumberController.
 * The list (one category at a time) and wire:navigate handling come from CissCrud (public/js/employee/crud.js);
 * the summary loads with the page and after every Generate. Listeners are on document.
 */
(function () {
    'use strict';

    const STATUS_BADGES = { waiting: 'bg-secondary', serving: 'bg-success', served: 'bg-primary', calling: 'bg-warning text-dark', next: 'bg-info' };
    const PREFIXES = { Enrollment: 'ENRO-', 'Pre-register': 'PRE-', Printing: 'PRINT-', Evaluation: 'EVAL-' };
    const page = () => document.getElementById('queueNumbers');

    CissCrud.register({
        pageId: 'queueNumbers',
        table: '#queueNumbersTable',
        order: [[0, 'asc']],
        ajaxData: () => ({ category: $('#queueNumbers .js-list-category').val() || '' }),
        columns: [
            { data: 'queue_number', render: $.fn.dataTable.render.text() },
            { data: 'status', render: (status, type) => type === 'display' ? CissCrud.badge(STATUS_BADGES[status] || 'bg-secondary', status) : status },
            { data: 'window', render: (window) => window ? 'Window ' + CissCrud.escapeHtml(window) : '' },
            { data: 'updated_at', render: $.fn.dataTable.render.text(), defaultContent: '' },
        ],
        tableOptions: {
            language: { emptyTable: 'Pick a category to list its numbers.' },
        },
    });

    function loadSummary() {
        if (!page()) return;
        $.get(page().dataset.summaryUrl).done(function (response) {
            $('#queueNumbers .js-summary-rows').html(response.data.length
                ? response.data.map((row) => '<tr>' +
                    '<td><strong>' + CissCrud.escapeHtml(row.category) + '</strong></td>' +
                    '<td>' + row.total + '</td>' +
                    '<td><small>' + CissCrud.escapeHtml(row.range) + '</small></td>' +
                    '<td>' + row.waiting + '</td>' +
                    '<td>' + row.serving + '</td>' +
                    '<td>' + row.served + '</td>' +
                    '</tr>').join('')
                : '<tr><td colspan="6" class="text-center text-muted">No numbers yet. Generate some.</td></tr>');
        });
    }

    document.addEventListener('livewire:navigated', loadSummary);

    $(document).on('change', '#queueNumbers .js-list-category', () => CissCrud.reload());

    // What the form will make, e.g. "ENRO-0001 to ENRO-1000 (1000 numbers)"
    $(document).on('input change', '#queueNumbersForm', function () {
        const elements = this.elements;
        const start = parseInt(elements.start.value, 10);
        const end = parseInt(elements.end.value, 10);
        const category = elements.catname.value;
        if (!category || !start || !end || end < start) return;

        const campus = (page().dataset.storeUrl.match(/campus=([^&]+)/) || [])[1] || '';
        const prefix = PREFIXES[category] || (category === 'Processing' ? decodeURIComponent(campus).toUpperCase() + 'P-' : '');
        const number = (n) => prefix + String(n).padStart(4, '0');
        $(this).find('.js-preview').text(number(start) + ' to ' + number(end) + ' (' + (end - start + 1) + ' numbers). Numbers that already exist are skipped.');
    });

    $(document).on('submit', '#queueNumbersForm', function (e) {
        e.preventDefault();
        const form = this;
        const $submit = $(form).find('[type="submit"]').prop('disabled', true);

        $.ajax({
            url: page().dataset.storeUrl,
            type: 'POST',
            data: $(form).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        })
            .done(function (response) {
                toastr.success(response.message);
                $('#queueNumbers .js-list-category').val(form.elements.catname.value);
                loadSummary();
                CissCrud.reload();
            })
            .fail(function (xhr) {
                const json = xhr.responseJSON || {};
                toastr.error(json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Could not generate the numbers.'));
            })
            .always(() => $submit.prop('disabled', false));
    });
})();
