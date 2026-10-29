/**
 * Enrollment > Enroll Student, search page (resources/views/pages/employee/enrollment/studenroll/enroll.blade.php).
 *  - Search: checked by AJAX (EnrollStudentController@findStudent); when the student can enroll, the
 *    enrollment form opens with wire:navigate (no page reload).
 *  - Queue panel and List of Pre-Enrolled Students: EnrollmentQueueController.
 * Pushed to <head>, so with wire:navigate it loads once: handlers are delegated (bound once), the table is
 * built on every livewire:navigated that lands on the page and destroyed when leaving.
 */
(function () {
    'use strict';

    const REFRESH_MS = 30000; // Pre-Enrolled list refresh

    let table = null;
    let refreshTimer = null;

    const page = () => document.getElementById('enrollStudent');

    function errorMessage(xhr, fallback) {
        const json = xhr.responseJSON || {};
        return json.errors ? Object.values(json.errors)[0][0] : (json.message || fallback);
    }

    // POST to one of the page's data-*-url (method PUT via _method when given)
    function send(urlKey, data, method) {
        return $.ajax({
            url: page().dataset[urlKey],
            type: 'POST',
            data: Object.assign({}, data, method ? { _method: method } : {}),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        });
    }

    // ---------- Search ----------

    // Student ID typed as 2024-0001-K: letters/digits only, dashes added after 4 and 8 characters
    function formatStudentId(value) {
        const clean = value.replace(/[^A-Za-z0-9]/g, '').toUpperCase().slice(0, 9);
        return [clean.slice(0, 4), clean.slice(4, 8), clean.slice(8, 9)].filter(Boolean).join('-');
    }

    $(document).on('input', '#enrollSearchForm .js-student-id', function () {
        this.value = formatStudentId(this.value);
    });

    $(document).on('submit', '#enrollSearchForm', function (e) {
        e.preventDefault();

        const $submit = $(this).find('[type="submit"]').prop('disabled', true);

        $.get(page().dataset.findUrl, $(this).serialize())
            .done((response) => Livewire.navigate(response.url))
            .fail(function (xhr) {
                toastr.error(errorMessage(xhr, 'Could not search the student.'));
                $submit.prop('disabled', false);
            });
    });

    // ---------- Queue panel (partials/queue-panel.blade.php) ----------

    // Show the card of a counter status: 1 Closed, 2 Open (Current Serving + Select Transaction), 3 Hold
    function showQueueStatus(status) {
        $('#queuePanel .js-queue-status').each(function () {
            this.classList.toggle('d-none', this.dataset.status !== String(status));
        });
    }

    // Change Control Status: save it, then show its card (on failure the select goes back)
    $(document).on('focus', '#counterStatusSelect', function () {
        this.dataset.saved = this.value;
    });

    $(document).on('change', '#counterStatusSelect', function () {
        const select = this;
        select.disabled = true;

        send('queueStatusUrl', { counterstatus: select.value }, 'PUT')
            .done(function (response) {
                select.dataset.saved = select.value;
                showQueueStatus(select.value);
                toastr.success(response.message);
            })
            .fail(function (xhr) {
                select.value = select.dataset.saved;
                toastr.error(errorMessage(xhr, 'Could not change the queue status.'));
            })
            .always(() => { select.disabled = false; });
    });

    // Next / Call: show the number in Current Serving
    function serve(button, urlKey) {
        button.disabled = true;

        send(urlKey)
            .done(function (response) {
                $('#queueNumber').val(response.queue_number);
                toastr.success(response.message);
            })
            .fail((xhr) => toastr.error(errorMessage(xhr, 'Could not reach the queue.')))
            .always(() => { button.disabled = false; });
    }

    $(document).on('click', '#nextButton', function () {
        serve(this, 'queueNextUrl');
    });

    $(document).on('click', '#callButton', function () {
        serve(this, 'queueCallUrl');
    });

    // Save Transaction: the category the counter serves (Next takes numbers of this category)
    $(document).on('submit', '#transactionForm', function (e) {
        e.preventDefault();

        const $submit = $(this).find('[type="submit"]').prop('disabled', true);

        send('queueCategoryUrl', { category: this.elements.category.value }, 'PUT')
            .done((response) => toastr.success(response.message))
            .fail((xhr) => toastr.error(errorMessage(xhr, 'Could not save the transaction.')))
            .always(() => $submit.prop('disabled', false));
    });

    // ---------- List of Pre-Enrolled Students (partials/pre-enrolled-list.blade.php) ----------

    document.addEventListener('livewire:navigated', function () {
        if (!document.getElementById('queueTable')) return;

        const text = $.fn.dataTable.render.text();

        table = $('#queueTable').DataTable({
            ajax: page().dataset.preEnrolledUrl,
            responsive: true,
            order: [[0, 'asc']], // oldest first, like the queue
            language: { emptyTable: 'No pre-enrolled students.' },
            columns: [
                { data: 'date', render: (date, type, row) => type === 'sort' ? row.date_sort : CissCrud.escapeHtml(date) },
                { data: 'student_id', render: text },
                { data: 'name', render: text },
                { data: 'course', render: text },
                { data: 'campus', render: text },
                {
                    data: 'status_label',
                    render: (label, type, row) => type === 'display'
                        ? CissCrud.badge(row.status === 3 ? 'bg-success-subtle text-success' : 'bg-info-subtle text-dark', label)
                        : label,
                },
                { data: null, orderable: false, searchable: false, defaultContent: '' }, // Action: with Edit Enrollment
            ],
        });

        refreshTimer = setInterval(() => table && table.ajax.reload(null, false), REFRESH_MS);
    });

    document.addEventListener('livewire:navigating', function () {
        clearInterval(refreshTimer);
        if (table) {
            table.destroy();
            table = null;
        }
    });
})();
