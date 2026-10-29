/**
 * The enrollment form of Enroll Student (enrollstudent.blade.php, EnrollmentFormController) and
 * Edit Enrollment (editenrollstudent.blade.php, EditEnrollmentController); see partials/enrollment-form.
 *
 * Flow: pick a Course Year & Section (its subjects load) -> add / remove subjects -> Assess (the server computes
 * the fees) -> Save. Changing the section, subjects, Status, Type or Transferee/Shiftee clears the assessment,
 * so Save is only possible right after an Assess of what is on screen. The server checks everything again.
 *
 * The page (#enrollmentForm) tells the differences:
 *   data-subjects        subjects shown when the page opens (Edit: the saved ones)
 *   data-save-url / -method   POST (enroll) or PUT (edit)
 *   data-lock-after-save 1: the form locks after saving (enroll); 0: it stays editable (edit)
 *   data-done-print-url / data-delete-url   Edit only: Done Print and Delete Enrollment
 *
 * Pushed to <head>, so with wire:navigate it loads once: handlers are delegated (bound once) and the page state
 * is reset on every livewire:navigated that lands on the form.
 */
(function () {
    'use strict';

    let subjects = new Map(); // subject id => row (code, name, title, units, lecFee, labFee, devFee, itSubject)

    const page = () => document.getElementById('enrollmentForm');
    const form = () => document.getElementById('enrollForm');
    const money = (value) => Number(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const escape = (text) => CissCrud.escapeHtml(text);

    document.addEventListener('livewire:navigated', function () {
        if (!page()) return;

        subjects = new Map(JSON.parse(page().dataset.subjects || '[]').map((subject) => [subject.id, subject]));
        renderSubjectRows(); // Edit: the saved subjects; the saved fees stay until the next Assess
        $('#subjectSelect').select2({ dropdownParent: $('#addSubjectModal'), width: '100%' });
        setUpValidation();
    });

    // ---------- Validation (jQuery Validation, loaded in employee-assets) ----------

    // Assess and Save both check every required field (so everything missing is highlighted at once)
    const MESSAGES = {
        class_id: 'Please select the Course Year & Section.',
        studLevel: 'Please select the Student Level.',
        studSch: 'Please select the Scholarship.',
        studMajor: 'Please select the Major.',
        studMinor: 'Please select the Minor.',
        studStatus: 'Please select the Status.',
        studType: 'Please select the Type.',
        transferee: 'Please select Transferee/Shiftee.',
        fourPs: "Please select if 4P's Beneficiary.",
    };

    // Required fields come from the `required` attributes; a missing one is outlined red with its message below
    function setUpValidation() {
        $(form()).validate({
            ignore: [],
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            messages: Object.fromEntries(Object.entries(MESSAGES).map(([name, text]) => [name, { required: text }])),
            highlight: (element) => element.classList.add('is-invalid'),
            unhighlight: (element) => element.classList.remove('is-invalid'),
            errorPlacement: (error, element) => error.insertAfter(element),
        });
    }

    // Checks every required field; on a missing one: scroll to it, focus it and tell the user
    function fieldsAreValid() {
        const elements = Array.from(form().querySelectorAll('select[required], input[required]'));

        const invalid = elements.filter((element) => !$(element).valid());
        if (!invalid.length) return true;

        invalid[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        invalid[0].focus();
        Swal.fire({
            icon: 'warning',
            title: 'Missing input',
            html: 'Please complete the highlighted field(s):<br><b>' +
                invalid.map((element) => escape(fieldLabel(element))).join('<br>') + '</b>',
        });

        return false;
    }

    // The field's label text without the "*"
    function fieldLabel(element) {
        const label = element.closest('[class*="col-"]').querySelector('label');
        return label ? label.textContent.replace('*', '').replace(':', '').trim() : element.name;
    }

    // A highlighted field is checked again as soon as it is changed, so the red outline goes away once filled
    $(document).on('change', '#enrollForm select.is-invalid, #enrollForm input.is-invalid', function () {
        $(this).valid();
    });

    // Server validation errors (422) on form fields: highlight them like the browser checks
    function showFieldErrors(xhr) {
        const errors = (xhr.responseJSON || {}).errors || {};
        const fieldErrors = {};

        Object.keys(errors).forEach(function (name) {
            if (form().elements[name]) fieldErrors[name] = errors[name][0];
        });

        if (Object.keys(fieldErrors).length) $(form()).validate().showErrors(fieldErrors);
    }

    // ---------- Requests ----------

    function errorMessage(xhr, fallback) {
        const json = xhr.responseJSON || {};
        return json.errors ? Object.values(json.errors)[0][0] : (json.message || fallback);
    }

    // The form's choices + the subject ids on screen
    function payload() {
        return $(form()).serialize() + '&' + $.param({ subject_ids: Array.from(subjects.keys()) });
    }

    // POST with Laravel's _method (PUT / DELETE) when given
    function post(url, data, method) {
        if (method && method !== 'POST') data = (data ? data + '&' : '') + '_method=' + method;
        return $.ajax({ url, type: 'POST', data, headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    }

    // ---------- Subjects table ----------

    // A change of subjects needs a new Assess
    function renderSubjects() {
        renderSubjectRows();
        clearAssessment();
    }

    function renderSubjectRows() {
        const rows = Array.from(subjects.values());

        $('#subjectTable tbody').html(rows.length
            ? rows.map((subject) => '<tr data-id="' + subject.id + '">' +
                '<td>' + escape(subject.code) + '</td>' +
                '<td>' + escape(subject.name) + '</td>' +
                '<td>' + escape(subject.title) + '</td>' +
                '<td class="text-end">' + subject.units + '</td>' +
                '<td class="text-end">' + money(subject.lecFee) + '</td>' +
                '<td class="text-end">' + money(subject.labFee) + '</td>' +
                '<td class="text-end">' + money(subject.devFee) + '</td>' +
                '<td class="text-center">' + (subject.itSubject ? 'Yes' : 'No') + '</td>' +
                '<td><button type="button" class="btn btn-outline-danger btn-sm js-remove-subject js-lockable" title="Remove"><i class="fas fa-trash"></i></button></td>' +
                '</tr>').join('')
            : '<tr class="js-empty"><td colspan="9" class="text-center text-muted py-3">No subjects. Select a Course Year &amp; Section or add a subject.</td></tr>');

        // Totals shown while editing (the saved units and fees are computed by the server)
        const sum = (key) => rows.reduce((total, subject) => total + Number(subject[key] || 0), 0);
        $('.js-total-units').val(sum('units'));
        $('.js-total-lec').val(money(Math.round(sum('lecFee'))));
        $('.js-total-lab').val(money(Math.round(sum('labFee'))));
        $('.js-total-dev').val(money(Math.round(sum('devFee'))));
        $('.js-it-subject').val(rows.some((subject) => subject.itSubject) ? 'Yes' : 'No');
    }

    // ---------- Assessment ----------

    function clearAssessment() {
        $('#feeTable tbody').html('<tr class="js-empty"><td colspan="3" class="text-center text-muted py-3">Click Assess to compute the fees.</td></tr>');
        $('#feeTable .js-fee-total').text(money(0));
        $('#saveButton').prop('disabled', true);
    }

    function renderAssessment(fees) {
        $('#feeTable tbody').html(fees.lines.map((line) => '<tr>' +
            '<td>' + escape(line.fund) + '</td>' +
            '<td>' + escape(line.account) + '</td>' +
            '<td class="text-end">' + money(line.amount) + '</td>' +
            '</tr>').join(''));
        $('#feeTable .js-fee-total').text(money(fees.total));
        $('#saveButton').prop('disabled', false);
    }

    // ---------- Handlers (delegated, bound once) ----------

    // Course Year & Section: refuse a full section, otherwise load its subjects (replacing the list)
    $(document).on('change', '#enrollForm select[name="class_id"]', function () {
        const select = this;
        const data = { schlyear: form().elements.schlyear.value, semester: form().elements.semester.value, class_id: select.value };

        $.get(page().dataset.sectionUrl, data)
            .done(function (response) {
                $('.js-program-name').val(response.class.programName);
                $('.js-year-level').val(response.class.yearLevel);
                subjects = new Map(response.subjects.map((subject) => [subject.id, subject]));
                renderSubjects();
            })
            .fail(function (xhr) {
                select.value = '';
                $('.js-program-name, .js-year-level').val('');
                subjects = new Map();
                renderSubjects();
                Swal.fire({ icon: 'error', title: 'Course Year & Section', text: errorMessage(xhr, 'Could not load the section.') });
            });
    });

    // Status, Type, Transferee/Shiftee change the fees
    $(document).on('change', '#enrollForm select[name="studStatus"], #enrollForm select[name="studType"], #enrollForm select[name="transferee"]', clearAssessment);

    $(document).on('click', '#addSubjectButton', function () {
        const option = $('#subjectSelect option:selected')[0];

        if (!option || !option.value) {
            toastr.warning('Select a subject.');
            return;
        }

        const subject = JSON.parse(option.dataset.row);
        if (subjects.has(subject.id)) {
            toastr.warning(subject.name + ' is already in the list.');
            return;
        }

        subjects.set(subject.id, subject);
        renderSubjects();
        $('#subjectSelect').val('').trigger('change');
        $('#addSubjectModal').modal('hide');
    });

    $(document).on('click', '#subjectTable .js-remove-subject', function () {
        const id = Number(this.closest('tr').dataset.id);

        Swal.fire({
            title: 'Remove this subject?',
            text: subjects.get(id).name,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, remove it',
        }).then((result) => {
            if (!result.isConfirmed) return;
            subjects.delete(id);
            renderSubjects();
        });
    });

    $(document).on('click', '#assessButton', function () {
        if (!fieldsAreValid()) return;

        if (!subjects.size) {
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'No subjects added.' });
            return;
        }

        const button = this;
        button.disabled = true;

        // Loading popup while the server computes the fees (can't be closed until it answers)
        Swal.fire({
            title: 'Assessing fees...',
            text: 'Please wait while the student fees are being fetched.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        post(page().dataset.assessUrl, payload())
            .done(function (fees) {
                renderAssessment(fees);
                Swal.fire({
                    icon: 'success',
                    title: 'Fees assessed',
                    text: fees.lines.length + ' fee line(s), total ' + money(fees.total) + '. Review them, then click Save.',
                    timer: 2000,
                    showConfirmButton: false,
                });
            })
            .fail(function (xhr) {
                clearAssessment();
                showFieldErrors(xhr);
                Swal.fire({ icon: 'warning', title: 'Assess', text: errorMessage(xhr, 'Could not assess the fees.') });
            })
            .always(() => { button.disabled = false; });
    });

    $(document).on('click', '#saveButton', function () {
        if (!fieldsAreValid()) return;

        const button = this;
        const isEdit = page().dataset.saveMethod === 'PUT';

        Swal.fire({
            title: isEdit ? 'Save the changes of this enrollment?' : 'Save this enrollment?',
            text: subjects.size + ' subject(s), total fees ' + $('#feeTable .js-fee-total').text(),
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: isEdit ? 'Yes, save' : 'Yes, enroll',
        }).then((result) => {
            if (!result.isConfirmed) return;

            button.disabled = true;

            post(page().dataset.saveUrl, payload(), page().dataset.saveMethod)
                .done(function (response) {
                    afterSave();
                    Swal.fire({ icon: 'success', title: isEdit ? 'Saved' : 'Enrolled', text: response.message });
                })
                .fail(function (xhr) {
                    button.disabled = false;
                    showFieldErrors(xhr);
                    const errors = (xhr.responseJSON || {}).errors || {};

                    // Full subjects: list them all
                    if (Array.isArray(errors.subject_ids) && errors.subject_ids.length > 1) {
                        Swal.fire({ icon: 'error', title: 'Subjects Full', html: errors.subject_ids.map(escape).join('<br>') });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Not saved', text: errorMessage(xhr, 'Could not save the enrollment.') });
                    }
                });
        });
    });

    // After saving, Print RF is enabled. Enroll: nothing can change anymore (Enroll New starts the next one).
    // Edit: the form stays editable; another change needs a new Assess before Save.
    function afterSave() {
        $('#printRfButton').prop('disabled', false).attr('title', 'Print the Registration Form');

        if (page().dataset.lockAfterSave === '1') {
            $(form()).find('select, input').prop('disabled', true);
            $('#enrollmentForm .js-lockable, #saveButton').prop('disabled', true);
            $('#saveButton').text('Saved');
        } else {
            $('#saveButton').prop('disabled', true);
        }
    }

    // Print RF: the Registration Form PDF in the modal's iframe
    $(document).on('click', '#printRfButton', function () {
        CissCrud.openPdf('#registrationFormModal', page().dataset.studentName, page().dataset.rfUrl);
    });

    // ---------- Edit Enrollment only ----------

    // The term of the form, sent with Done Print / Delete (the server finds the enrollment by student + term)
    const termData = () => $.param({ schlyear: form().elements.schlyear.value, semester: form().elements.semester.value });

    // Done Print: "For Printing" -> enrolled
    $(document).on('click', '#donePrintButton', function () {
        const button = this;
        button.disabled = true;

        Swal.fire({ title: 'Processing...', text: 'Please wait while we process the enrollment.', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

        post(page().dataset.donePrintUrl, termData(), 'PUT')
            .done(function (response) {
                $(button).remove();
                Swal.fire({ icon: 'success', title: 'Done Print', text: response.message });
            })
            .fail(function (xhr) {
                button.disabled = false;
                Swal.fire({ icon: 'error', title: 'Error!', text: errorMessage(xhr, 'Failed to process the enrollment.') });
            });
    });

    // Delete Enrollment: confirm, delete, then back to the Edit Enrollment search
    $(document).on('click', '#deleteEnrollmentButton', function () {
        Swal.fire({
            title: 'Delete this enrollment?',
            text: "You won't be able to recover the enrollment of " + page().dataset.studentName + '.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            post(page().dataset.deleteUrl, termData(), 'DELETE')
                .done(function (response) {
                    Swal.fire({ icon: 'success', title: 'Deleted', text: response.message })
                        .then(() => Livewire.navigate(response.redirect));
                })
                .fail((xhr) => Swal.fire({ icon: 'error', title: 'Not deleted', text: errorMessage(xhr, 'Could not delete the enrollment.') }));
        });
    });
})();
