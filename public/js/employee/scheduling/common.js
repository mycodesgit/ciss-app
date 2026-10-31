/**
 * Helpers shared by the Scheduling pages (resources/views/pages/employee/scheduling). Tables, modal forms and
 * wire:navigate handling come from CissCrud (public/js/employee/crud.js); this file only adds what Scheduling needs:
 * search filters, select filling, campus checkboxes, the subject picker and the fee preview.
 * Fees shown in the forms are a preview: the server always computes the saved values (SubjectFees).
 */
window.CissScheduling = (function () {
    'use strict';

    // { campus, schlyear, semester, ... } of a search form (only the fields it has)
    function filters(formId) {
        const values = {};
        new FormData(document.getElementById(formId)).forEach((value, name) => { values[name] = value; });
        return values;
    }

    // Copies the searched filters into a modal form's hidden inputs of the same names (Add forms)
    function copyFilters(form, values) {
        Object.entries(values).forEach(([name, value]) => {
            const input = form.querySelector('input[type="hidden"][name="' + name + '"]');
            if (input) input.value = value;
        });
    }

    // Rebuilds a <select>: items = [{value, label}], keeps the first placeholder option if asked
    function fillSelect(select, items, placeholder) {
        const options = (placeholder ? [new Option(placeholder, '')] : [])
            .concat(items.map((item) => new Option(item.label, item.value)));
        $(select).empty().append(options);
        if (placeholder) select.options[0].disabled = true;
    }

    function checkCampuses(form, codes) {
        form.querySelectorAll('input[name="campus[]"]').forEach((box) => { box.checked = codes.includes(box.value); });
    }

    function departments(select, url, college, selected) {
        fillSelect(select, [], 'Loading...');
        if (!college) return fillSelect(select, [], '--Select--');

        return $.get(url, { college })
            .done(function (rows) {
                fillSelect(select, rows.map((row) => ({ value: row.deptCod, label: row.deptName })), '--Select--');
                select.value = selected || '';
            })
            .fail(() => fillSelect(select, [], '--Select--'));
    }

    // Select2 subject search (SchedulingLookupController::subjects) inside a modal
    function subjectPicker(select, url) {
        const $select = $(select);
        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');

        $select.select2({
            theme: 'bootstrap4',
            width: '100%',
            dropdownParent: $select.closest('.modal'),
            placeholder: 'Type a subject code, name or title',
            minimumInputLength: 2,
            ajax: { url, dataType: 'json', delay: 300, data: (params) => ({ q: params.term }) },
        });
    }

    // Puts one known subject in a picker (Edit) without searching; only Select2 is told (no fee preview / fund pick)
    function setSubject(select, code, text) {
        $(select).empty().append(new Option(text, code, true, true)).trigger('change.select2');
    }

    /**
     * Fee preview of a form: GET the lookup URL (__CODE__ = subject code) with the form's isOJT / lecFee and
     * fill the read-only fields named lecUnit, labUnit, subUnit, lecFee, labFee, devFee. Graduate school
     * subjects unlock lecFee. setFund: also pick the suggested fund (when the subject changes).
     */
    function previewFees(form, url, setFund) {
        const code = form.elements.subCode.value;
        if (!code) return;

        const lecFee = form.elements.lecFee;
        $.get(url.replace('__CODE__', encodeURIComponent(code)), {
            isOJT: form.elements.isOJT.value,
            lecFee: lecFee.readOnly ? '' : lecFee.value,
        }).done(function (fees) {
            ['lecUnit', 'labUnit', 'subUnit', 'labFee', 'devFee'].forEach((name) => { form.elements[name].value = fees[name]; });
            lecFee.readOnly = !fees.graduate;
            if (!fees.graduate) lecFee.value = fees.lecFee;
            if (setFund) form.elements.fund.value = fees.fund || '';
        }).fail((xhr) => toastr.error((xhr.responseJSON || {}).message || 'Could not read the subject.'));
    }

    function campusLabel(codes) {
        return CissCrud.escapeHtml(codes.join(', '));
    }

    return { filters, copyFilters, fillSelect, checkCampuses, departments, subjectPicker, setSubject, previewFees, campusLabel };
})();
