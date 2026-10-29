/**
 * Enrollment > Edit Dup. Fees, a student's fee lines (resources/views/pages/employee/enrollment/dupfees/show.blade.php).
 * Server: DuplicateFeesController. Lines whose fund + account appear more than once are marked Duplicate and
 * can be deleted one copy at a time (the server keeps the last copy and refuses lines of other enrollments).
 * The search page uses enroll.js. Pushed to <head>, so with wire:navigate it loads once (handlers bound once).
 */
(function () {
    'use strict';

    const page = () => document.getElementById('duplicateFees');
    const money = (value) => Number(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const escape = (text) => CissCrud.escapeHtml(text);

    function errorMessage(xhr, fallback) {
        const json = xhr.responseJSON || {};
        return json.errors ? Object.values(json.errors)[0][0] : (json.message || fallback);
    }

    document.addEventListener('livewire:navigated', function () {
        if (page()) loadFees();
    });

    function loadFees() {
        $.get(page().dataset.listUrl)
            .done(renderFees)
            .fail((xhr) => toastr.error(errorMessage(xhr, 'Could not load the fees.')));
    }

    function renderFees(response) {
        const lines = response.data;
        const duplicates = lines.filter((line) => line.duplicate).length;

        $('#duplicateFeesTable tbody').html(lines.length
            ? lines.map((line) => '<tr data-id="' + line.id + '"' + (line.duplicate ? ' class="table-warning"' : '') + '>' +
                '<td>' + escape(line.fundID) + '</td>' +
                '<td>' + escape(line.account) + (line.duplicate ? ' ' + CissCrud.badge('bg-warning text-dark', 'Duplicate') : '') + '</td>' +
                '<td class="text-end">' + money(line.amount) + '</td>' +
                '<td class="text-center">' + line.copies + '</td>' +
                '<td>' + (line.duplicate
                    ? '<button type="button" class="btn btn-outline-danger btn-sm js-delete-fee" title="Delete this copy"><i class="fas fa-trash"></i></button>'
                    : '') + '</td>' +
                '</tr>').join('')
            : '<tr><td colspan="5" class="text-center text-muted py-3">No fees saved for this enrollment.</td></tr>');

        $('#duplicateFeesTable .js-fee-total').text(money(response.total));
        $('#duplicateFees .js-duplicate-summary').text(duplicates
            ? duplicates + ' line(s) are duplicates'
            : 'No duplicate fees');
    }

    // Delete one copy of a duplicated fee, then reload the list (copies and total change)
    $(document).on('click', '#duplicateFeesTable .js-delete-fee', function () {
        const row = this.closest('tr');
        const account = row.cells[1].firstChild.textContent.trim();

        Swal.fire({
            title: 'Delete this duplicate?',
            text: account + ' - ' + row.cells[2].textContent,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: page().dataset.deleteUrl.replace('__ID__', row.dataset.id),
                type: 'POST',
                data: { _method: 'DELETE' },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            })
                .done(function (response) {
                    toastr.success(response.message);
                    loadFees();
                })
                .fail((xhr) => Swal.fire({ icon: 'error', title: 'Not deleted', text: errorMessage(xhr, 'Could not delete the fee line.') }));
        });
    });

    // Print RF: the Registration Form PDF in the modal's iframe
    $(document).on('click', '#duplicateFees #printRfButton', function () {
        CissCrud.openPdf('#registrationFormModal', page().dataset.studentName, page().dataset.rfUrl);
    });
})();
