/**
 * Enrollment > Transferred Student (resources/views/pages/employee/enrollment/transfer/index.blade.php).
 * Server: StudentTransferController. Find a student -> see their campuses / last enrollment -> pick the campus
 * -> confirm -> transfer; the list reloads. The list table and wire:navigate handling come from CissCrud.
 */
(function () {
    'use strict';

    const escape = (text) => CissCrud.escapeHtml(text);
    const page = () => document.getElementById('studentTransfers');

    function errorMessage(xhr, fallback) {
        const json = xhr.responseJSON || {};
        return json.errors ? Object.values(json.errors)[0][0] : (json.message || fallback);
    }

    CissCrud.register({
        pageId: 'studentTransfers',
        table: '#transfersTable',
        order: [[3, 'desc']],
        columns: [
            { data: 'name', render: $.fn.dataTable.render.text() },
            { data: 'stud_id', render: $.fn.dataTable.render.text() },
            { data: null, render: (data, type, row) => escape(row.from) + ' &rarr; ' + escape(row.to) },
            { data: 'date', render: (date, type, row) => type === 'sort' ? row.date_sort : escape(date || '') },
            { data: 'by', render: $.fn.dataTable.render.text() },
        ],
        tableOptions: { language: { emptyTable: 'No transferred students yet.' } },
        actions: {},
    });

    function resetTransferForm() {
        const form = document.getElementById('transferForm');
        form.reset();
        form.classList.add('d-none');
    }

    // Find: show the student and the campuses they can be transferred to
    $(document).on('submit', '#transferLookupForm', function (e) {
        e.preventDefault();
        resetTransferForm();

        $.get(page().dataset.lookupUrl, $(this).serialize())
            .done(function (student) {
                const form = document.getElementById('transferForm');
                const $form = $(form);

                form.elements.stud_id.value = student.stud_id;
                $form.find('.js-name').text(student.name);
                $form.find('.js-from').text(student.fromName);
                $form.find('.js-campuses').text(student.campuses.join(', '));
                $form.find('.js-last').text(student.lastEnrollment || 'None');
                $form.find('.js-enrolled-now').toggleClass('d-none', !student.enrolledNow);
                $(form.elements.tocampus).html('<option value="" selected disabled>Select campus</option>' +
                    student.destinations.map((campus) => '<option value="' + escape(campus.code) + '">' + escape(campus.name) + '</option>').join(''));

                form.classList.remove('d-none');
            })
            .fail((xhr) => toastr.error(errorMessage(xhr, 'Could not find the student.')));
    });

    $(document).on('click', '#transferForm .js-cancel-transfer', resetTransferForm);

    // Transfer: confirm, save, reload the list
    $(document).on('submit', '#transferForm', function (e) {
        e.preventDefault();

        const form = this;
        const name = $(form).find('.js-name').text();
        const from = $(form).find('.js-from').text();
        const to = $(form.elements.tocampus).find('option:selected').text();

        Swal.fire({
            title: 'Transfer this student?',
            html: escape(name) + '<br><b>' + escape(from) + ' &rarr; ' + escape(to) + '</b><br>' +
                '<small class="text-muted">The student\'s records (enrollments, grades, fees) will also be visible to ' + escape(to) + '.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Yes, transfer',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            const $submit = $(form).find('[type="submit"]').prop('disabled', true);

            $.ajax({
                url: page().dataset.storeUrl,
                type: 'POST',
                data: $(form).serialize(),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            })
                .done(function (response) {
                    Swal.fire({ icon: 'success', title: 'Transferred', text: response.message });
                    resetTransferForm();
                    document.getElementById('transferLookupForm').reset();
                    CissCrud.reload();
                })
                .fail((xhr) => Swal.fire({ icon: 'error', title: 'Not transferred', text: errorMessage(xhr, 'Could not transfer the student.') }))
                .always(() => $submit.prop('disabled', false));
        });
    });
})();
