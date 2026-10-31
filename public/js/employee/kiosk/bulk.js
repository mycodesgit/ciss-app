/**
 * Kiosk > Bulk Generation (resources/views/pages/employee/kiosk/bulk/index.blade.php). Server: KioskBulkController.
 * Search a term -> its classes (CissCrud table). View opens a class's students; Generate posts to the server, which
 * sets the passwords and answers with the password list as a PDF, shown in the PDF modal from a blob URL (the
 * passwords are not saved readable anywhere, so this PDF is the only copy). Listeners are on document (wire:navigate).
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const page = () => document.getElementById('kioskBulk');
    const form = () => document.getElementById('kioskBulkSearchForm');

    let shownClass = null; // the class open in the students modal
    let pdfUrl = null;     // blob URL of the last passwords PDF (revoked when its modal closes)

    function errorMessage(json, fallback) {
        return json && json.errors ? Object.values(json.errors)[0][0] : ((json && json.message) || fallback);
    }

    CissCrud.register({
        pageId: 'kioskBulk',
        table: '#kioskBulkTable',
        order: [[2, 'asc'], [3, 'asc']],
        ajaxData: () => {
            const elements = form().elements;
            return { campus: elements.campus.value, schlyear: elements.schlyear.value, semester: elements.semester.value };
        },
        columns: [
            { data: 'progCod', render: text },
            { data: 'progName', render: text, defaultContent: '' },
            { data: 'progAcronym', render: text, defaultContent: '' },
            { data: 'section', render: text },
            { data: 'total', render: (total, type) => type === 'display' ? '<strong>' + total + '</strong>' : total },
            { data: 'male' },
            { data: 'female' },
            {
                data: 'accounts',
                render: (accounts, type, row) => type === 'display'
                    ? CissCrud.badge(accounts === row.total ? 'bg-success' : (accounts ? 'bg-warning text-dark' : 'bg-secondary'), accounts + ' / ' + row.total)
                    : accounts,
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => CissCrud.actionsMenu([{ action: 'students', icon: 'fas fa-eye', label: 'View Students' }]),
            },
        ],
        tableOptions: {
            language: { emptyTable: 'No classes to show. Select a campus, academic year and semester, then click Search.' },
        },
        actions: {
            students(row) {
                shownClass = row;
                $('#kioskBulkStudentsModal .js-class-name').text((row.progAcronym || row.progCod) + ' ' + row.section);
                loadStudents().done(() => $('#kioskBulkStudentsModal').modal('show'));
            },
        },
    });

    function loadStudents() {
        const $modal = $('#kioskBulkStudentsModal');

        return $.get(page().dataset.studentsUrl, shownClass.class)
            .done(function (response) {
                const missing = response.data.filter((student) => !student.hasAccount).length;

                $modal.find('.js-missing-count').text(missing);
                $modal.find('.js-all-count').text(response.data.length);
                $modal.find('.js-generate[data-mode="missing"]').prop('disabled', !missing);
                $modal.find('.js-generate[data-mode="all"]').prop('disabled', !response.data.length);
                $modal.find('.js-student-rows').html(response.data.length
                    ? response.data.map((student, i) => '<tr>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + CissCrud.escapeHtml(student.studentID) + '</td>' +
                        '<td>' + CissCrud.escapeHtml(student.name) + '</td>' +
                        '<td>' + CissCrud.escapeHtml(student.gender || '') + '</td>' +
                        '<td>' + (student.hasAccount ? CissCrud.badge('bg-success', 'Has account') : CissCrud.badge('bg-secondary', 'None')) + '</td>' +
                        '</tr>').join('')
                    : '<tr><td colspan="5" class="text-center text-muted">No students in this class.</td></tr>');
            })
            .fail((xhr) => toastr.error(errorMessage(xhr.responseJSON, 'Could not load the students.')));
    }

    function generate(mode, $button) {
        const data = new FormData();
        Object.entries(Object.assign({ mode }, shownClass.class)).forEach(([key, value]) => data.append(key, value));
        $button.prop('disabled', true);

        fetch(page().dataset.generateUrl, {
            method: 'POST',
            body: data,
            // Accept JSON so a refusal (validation, nothing to generate) comes back as JSON; success is still the PDF
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(async function (response) {
                if (!response.ok) {
                    const json = await response.json().catch(() => null);
                    throw new Error(errorMessage(json, 'Could not generate the passwords.'));
                }
                return response.blob();
            })
            .then(function (pdf) {
                if (pdfUrl) URL.revokeObjectURL(pdfUrl);
                pdfUrl = URL.createObjectURL(pdf);

                $('#kioskBulkStudentsModal').modal('hide');
                CissCrud.openPdf('#kioskPasswordsPdfModal', (shownClass.progAcronym || shownClass.progCod) + ' ' + shownClass.section, pdfUrl);
                toastr.success('Passwords saved. Print or save the PDF now: it is the only copy of the passwords.');
                CissCrud.reload();
            })
            .catch((error) => toastr.error(error.message))
            .finally(() => $button.prop('disabled', false));
    }

    $(document).on('click', '#kioskBulkStudentsModal .js-generate', function () {
        const $button = $(this);
        const mode = this.dataset.mode;
        const count = $button.find(mode === 'all' ? '.js-all-count' : '.js-missing-count').text();

        Swal.fire({
            title: mode === 'all' ? 'Reset every password of this class?' : 'Generate kiosk passwords?',
            text: mode === 'all'
                ? 'All ' + count + ' students get a new password; their current passwords stop working.'
                : count + ' student(s) without a kiosk account get one.',
            icon: mode === 'all' ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: mode === 'all' ? '#d33' : '#198754',
            confirmButtonText: 'Yes, generate',
        }).then((result) => {
            if (result.isConfirmed) generate(mode, $button);
        });
    });

    // Forget the passwords PDF when its modal closes
    $(document).on('hidden.bs.modal', '#kioskPasswordsPdfModal', function () {
        $(this).find('.js-pdf-frame').removeAttr('src');
        if (pdfUrl) URL.revokeObjectURL(pdfUrl);
        pdfUrl = null;
    });

    $(document).on('submit', '#kioskBulkSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });
})();
