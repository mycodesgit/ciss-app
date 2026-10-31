/**
 * Scheduling > Faculty List (resources/views/pages/employee/scheduling/faculty/index.blade.php). Server: FacultyController.
 * Table, form saving and wire:navigate handling come from CissCrud (public/js/employee/crud.js).
 *  - Add Newly Hired / Edit: college -> its departments; the Major field only for the major department (SED)
 *  - Assign Existing Faculty: search faculty of other campuses (Select2 + AJAX); the picked one is the form's record
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const FORM = 'schedFacultySearchForm';
    const page = () => document.getElementById('schedFaculty');
    let searched = {};

    function campusName() {
        const option = document.querySelector('#' + FORM + ' select[name="campus"] option[value="' + searched.campus + '"]');
        return option ? option.textContent.trim() : searched.campus;
    }

    function toggleMajor(form) {
        const show = form.elements.facdept.value === page().dataset.majorDepartment;
        $(form).find('.js-major').toggleClass('d-none', !show);
        form.elements.deptmajor.required = show;
        if (!show) form.elements.deptmajor.value = '';
    }

    function loadDepartments(form, selected) {
        const request = CissScheduling.departments(form.elements.facdept, page().dataset.departmentsUrl, form.elements.faccollege.value, selected);
        if (request) request.always(() => toggleMajor(form));
        else toggleMajor(form);
    }

    CissCrud.register({
        pageId: 'schedFaculty',
        table: '#schedFacultyTable',
        order: [[0, 'asc']],
        ajaxData: () => (searched = CissScheduling.filters(FORM)),
        columns: [
            { data: 'name', render: text },
            { data: 'prefix_label', render: text },
            { data: 'faccollege', render: text, defaultContent: '' },
            { data: 'department', render: text },
            { data: 'rank', render: text, defaultContent: '' },
            { data: 'campuses', render: text },
            { data: 'active_campus', render: text },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => CissCrud.actionsMenu([{ action: 'edit', icon: 'fas fa-pen', label: 'Edit' }]),
            },
        ],
        tableOptions: { language: { emptyTable: 'No faculty in this campus yet.' } },
        actions: {
            edit(member) {
                const { $modal, form } = CissCrud.openFor('#editFacultyModal', member);
                CissCrud.fill(form, member, ['faccollege', 'deptmajor', 'lname', 'fname', 'mname', 'ext', 'prefix', 'suffix', 'email', 'rank']);
                loadDepartments(form, member.facdept);
                $modal.modal('show');
            },
        },
    });

    $(document).on('submit', '#' + FORM, function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    $(document).on('change', '#schedFaculty .js-college', function () {
        loadDepartments(this.form);
    });
    $(document).on('change', '#schedFaculty .js-department', function () {
        toggleMajor(this.form);
    });

    // Add Newly Hired: empty, for the searched campus
    $(document).on('show.bs.modal', '#addFacultyModal', function (e) {
        if (e.target !== this) return;
        const { form } = CissCrud.openFor('#addFacultyModal');
        CissScheduling.copyFilters(form, searched);
        loadDepartments(form);
    });

    // Assign Existing Faculty: search other campuses' faculty; the picked one is the record the form saves
    $(document).on('show.bs.modal', '#assignFacultyModal', function (e) {
        if (e.target !== this) return;
        const { form } = CissCrud.openFor('#assignFacultyModal');
        CissScheduling.copyFilters(form, searched);
        $(this).find('.js-assign-campus').val(campusName());

        const $select = $(this).find('.js-existing-faculty');
        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        $select.empty().select2({
            theme: 'bootstrap4',
            width: '100%',
            dropdownParent: $(this),
            placeholder: 'Type a last or first name',
            minimumInputLength: 2,
            ajax: {
                url: page().dataset.searchUrl,
                dataType: 'json',
                delay: 300,
                data: (params) => ({ q: params.term, campus: searched.campus }),
            },
        });
    });

    $(document).on('change', '#assignFacultyForm .js-existing-faculty', function () {
        this.form.dataset.recordId = this.value || '';
    });
})();
