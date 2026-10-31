/**
 * Scheduling > Classes Enrolled (resources/views/pages/employee/scheduling/classes/index.blade.php).
 * Server: ClassEnrollController. Table, form saving and wire:navigate handling come from CissCrud
 * (public/js/employee/crud.js). The list response also carries the searched campus's programs, which fill the
 * Add / Edit selects; new sections go to the searched campus and term.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const FORM = 'schedClassesSearchForm';
    let searched = {};
    let programs = [];

    function termLabel() {
        const semester = document.querySelector('#' + FORM + ' select[name="semester"] option[value="' + searched.semester + '"]');
        return semester ? semester.textContent + ' ' + searched.schlyear + ' (' + searched.campus + ')' : '';
    }

    function fillPrograms(form, selected) {
        CissScheduling.fillSelect(form.elements.progCode, programs.map((p) => ({ value: p.progCod, label: p.progAcronym })), '--Select--');
        form.elements.progCode.value = selected || '';
    }

    CissCrud.register({
        pageId: 'schedClasses',
        table: '#schedClassesTable',
        order: [[0, 'asc'], [1, 'asc']],
        ajaxData: () => (searched = CissScheduling.filters(FORM)),
        columns: [
            { data: 'progAcronym', render: text },
            { data: 'classSection', render: text },
            { data: 'classno', render: text },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => CissCrud.actionsMenu([
                    { action: 'edit', icon: 'fas fa-pen', label: 'Edit' },
                    { action: 'destroy', icon: 'fas fa-trash', label: 'Delete', color: '#dc3545' },
                ]),
            },
        ],
        tableOptions: {
            language: { emptyTable: 'No sections in this campus and term yet.' },
            drawCallback: function () {
                programs = (this.api().ajax.json() || {}).programs || [];
                $('#schedClasses .js-term-shown').text(termLabel());
            },
        },
        actions: {
            edit(section) {
                const { $modal, form } = CissCrud.openFor('#editClassModal', section);
                fillPrograms(form, section.progCode);
                CissCrud.fill(form, section, ['classSection', 'classno']);
                $modal.find('.js-modal-term').text(termLabel());
                $modal.modal('show');
            },

            destroy(section) {
                Swal.fire({
                    title: 'Delete this section?',
                    text: section.progAcronym + ' ' + section.classSection + ' will be removed. A section with offered subjects cannot be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => {
                    if (result.isConfirmed) CissCrud.send('destroyUrl', section.id, 'DELETE');
                });
            },
        },
    });

    $(document).on('submit', '#' + FORM, function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    // Add New: empty, for the searched campus and term
    $(document).on('show.bs.modal', '#addClassModal', function (e) {
        if (e.target !== this) return;
        const { form } = CissCrud.openFor('#addClassModal');
        CissScheduling.copyFilters(form, searched);
        fillPrograms(form);
        $(this).find('.js-modal-term').text(termLabel());
    });
})();
