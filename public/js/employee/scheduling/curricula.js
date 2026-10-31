/**
 * Scheduling > Curriculum Programs (resources/views/pages/employee/scheduling/curricula/index.blade.php).
 * Server: CurriculumController. Table, Add form saving and wire:navigate handling come from CissCrud
 * (public/js/employee/crud.js). The table is empty until a program is searched; another campus reloads the page
 * (wire:navigate) with that campus's programs. Units and fees in the Add form are a server preview.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const FORM = 'schedCurriculaSearchForm';
    const page = () => document.getElementById('schedCurricula');
    let searched = {};

    function termLabel() {
        const form = document.getElementById(FORM);
        const program = form.querySelector('select[name="progCod"] option[value="' + CSS.escape(searched.progCod || '') + '"]');
        const semester = form.querySelector('select[name="semester"] option[value="' + searched.semester + '"]');
        return program && semester ? program.textContent.trim() + ' - ' + semester.textContent + ' (' + searched.campus + ')' : '';
    }

    function fillOjt(select, semester) {
        const options = JSON.parse(page().dataset.ojtOptions)[String(semester) === '3' ? 'summer' : 'regular'];
        CissScheduling.fillSelect(select, Object.entries(options).map(([value, label]) => ({ value, label })));
    }

    CissCrud.register({
        pageId: 'schedCurricula',
        table: '#schedCurriculaTable',
        order: [[0, 'asc'], [2, 'asc']],
        ajaxData: () => (searched = CissScheduling.filters(FORM)),
        columns: [
            { data: 'yrlvl', render: text },
            { data: 'subCode', render: text },
            { data: 'sub_name', render: (name, type, row) => type === 'display' ? CissCrud.escapeHtml(name) + '<small class="text-muted d-block">' + CissCrud.escapeHtml(row.sub_title) + '</small>' : name },
            { data: 'lecUnit', render: text },
            { data: 'labUnit', render: text },
            { data: 'subUnit', render: text },
            { data: null, orderable: false, render: (row) => CissCrud.escapeHtml([row.lecFee, row.labFee, row.devFee].map((fee) => fee || 0).join(' / ')) },
            { data: 'fundAccount', render: (account) => CissCrud.escapeHtml(account || 'No Account') },
            { data: 'itfee', render: text },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => CissCrud.actionsMenu([{ action: 'destroy', icon: 'fas fa-trash', label: 'Remove', color: '#dc3545' }]),
            },
        ],
        tableOptions: {
            language: { emptyTable: 'No subjects to show. Pick a program and semester, then click Search.' },
            drawCallback: function () {
                const ready = Boolean(searched.progCod);
                $('#schedCurricula .js-add, #schedCurricula .js-view-pdf').prop('disabled', !ready);
                $('#schedCurricula .js-term-shown').text(ready ? '- ' + termLabel() : '');
            },
        },
        actions: {
            destroy(row) {
                Swal.fire({
                    title: 'Remove this subject?',
                    text: row.subCode + ' ' + row.sub_name + ' will be removed from this curriculum.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it',
                }).then((result) => {
                    if (result.isConfirmed) CissCrud.send('destroyUrl', row.id, 'DELETE');
                });
            },
        },
    });

    $(document).on('submit', '#' + FORM, function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    // Another campus: its programs (page reload through wire:navigate, so the layout stays)
    $(document).on('change', '#' + FORM + ' .js-campus', function () {
        Livewire.navigate(page().dataset.indexUrl + '?campus=' + encodeURIComponent(this.value));
    });

    // Add Subject: empty, for the searched campus, program and semester
    $(document).on('show.bs.modal', '#addCurriculumModal', function (e) {
        if (e.target !== this) return;
        const { form } = CissCrud.openFor('#addCurriculumModal');
        CissScheduling.copyFilters(form, searched);
        fillOjt(form.elements.isOJT, searched.semester);
        form.elements.lecFee.readOnly = true;
        CissScheduling.subjectPicker(form.elements.subCode, page().dataset.subjectsUrl);
        CissScheduling.subjectPicker(form.elements.prerequisite, page().dataset.subjectsUrl);
        $(form).find('.js-subject, .js-prerequisite').val(null).trigger('change');
        $(this).find('.js-modal-term').text(termLabel());
    });

    // Fee preview: a new subject (also picks its suggested fund), OJT/Thesis, or a typed graduate lecture fee
    $(document).on('change', '#addCurriculumForm .js-subject', function () {
        CissScheduling.previewFees(this.form, page().dataset.feesUrl, true);
    });
    $(document).on('change', '#addCurriculumForm .js-fee-input', function () {
        CissScheduling.previewFees(this.form, page().dataset.feesUrl, false);
    });

    $(document).on('click', '#schedCurricula .js-view-pdf', function () {
        const url = page().dataset.pdfUrl + '?' + $.param({ campus: searched.campus, progCod: searched.progCod });
        CissCrud.openPdf('#curriculumPdfModal', termLabel().split(' - ')[0], url);
    });
})();
