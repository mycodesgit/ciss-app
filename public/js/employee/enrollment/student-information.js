/**
 * Enrollment > Student Information (resources/views/pages/employee/enrollment/student-info/index.blade.php).
 * Server: StudentInformationController. The results table and the save (toast, reload) come from CissCrud;
 * the address from CissAddress (public/js/employee/address.js). This file opens a student: loads the information
 * into the form and the enrollment history (Enrollment History's route) into the History tab.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const FIELDS = ['fname', 'mname', 'lname', 'ext', 'gender', 'civil_status', 'bday', 'pbirth', 'contact', 'email', 'religion',
        'region', 'province', 'city', 'brgy', 'hnum', 'zcode', 'address',
        'stud_father', 'stud_mother', 'stud_guardian', 'monthly_income', 'guardian_contact',
        'lstsch_attended', 'lst_sch_attended_year', 'suc_lst_attended'];

    const filters = () => {
        const form = document.getElementById('studentInfoSearchForm');
        if (!form) return {};
        return { query: form.elements.query.value.trim(), level: form.elements.level ? form.elements.level.value : 'undergraduate' };
    };

    CissCrud.register({
        pageId: 'studentInfo',
        table: '#studentInfoTable',
        order: [[1, 'asc']],
        ajaxData: filters,
        columns: [
            { data: 'stud_id', render: text },
            { data: 'name', render: text },
            { data: 'gender', render: text, defaultContent: '' },
            { data: 'campus', render: text },
            { data: 'civil_status', render: text, defaultContent: '' },
            { data: 'course', render: (course) => CissCrud.escapeHtml(course || 'No Course') },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => CissCrud.actionsMenu([{ action: 'view', icon: 'fas fa-eye', label: 'View / Edit' }]),
            },
        ],
        tableOptions: {
            language: { emptyTable: 'No students to show. Enter a last name, first name or Student ID No., then click OK.' },
            lengthChange: false,
            // Note when the search matched more students than the table shows
            drawCallback: function () {
                const json = this.api().ajax.json() || {};
                $('#studentInfo .js-limit-note').text(json.limited ? 'Showing the first ' + json.data.length + ' matches. Search more specifically.' : '');
            },
        },
        actions: {
            view(row) {
                CissCrud.fetchFor('showUrl', row.id).done(function (student) {
                    const { $modal, form } = CissCrud.openFor('#studentInfoModal', row);

                    // A saved value missing from a list (old records) is added, so saving doesn't clear it
                    ['gender', 'civil_status'].forEach((field) => keepOption(form.querySelector('[name="' + field + '"]'), student[field]));
                    CissCrud.fill(form, student, FIELDS);
                    showDetails($modal, student);
                    CissAddress.show(student);
                    loadHistory($modal, student.history_url);

                    $modal.find('.js-first-tab').tab('show');
                    $modal.modal('show');
                });
            },
        },
    });

    function keepOption(select, value) {
        if (value && !$(select).find('option').filter((i, option) => option.value === value).length) {
            $(select).append($('<option>').val(value).text(value));
        }
    }

    // Header and read-only fields (data-show="field")
    function showDetails($modal, student) {
        $modal.find('.js-student-name').text(student.name);
        $modal.find('.js-student-id').text(student.stud_id);
        $modal.find('.js-student-course').text(student.course || 'No Course');
        $modal.find('.js-student-campus').text(student.campus);
        $modal.find('.js-student-admission').text(student.date_admission || 'N/A');

        $modal.find('[data-show]').each(function () {
            const value = student[this.dataset.show] || '';
            if (this.tagName === 'INPUT') this.value = value;
            else this.textContent = value || 'N/A';
        });
    }

    // The student's enrollments (all terms), same data as Enrollment > Enrollment History
    function loadHistory($modal, url) {
        const $rows = $modal.find('.js-history-rows').html('<tr><td colspan="7" class="text-center text-muted">Loading...</td></tr>');

        $.get(url)
            .done(function (response) {
                $rows.html(response.data.length
                    ? response.data.map((history) => '<tr>' +
                        '<td>' + CissCrud.escapeHtml(history.studentID) + '</td>' +
                        '<td>' + CissCrud.escapeHtml(history.schlyear) + '</td>' +
                        '<td>' + CissCrud.badge(history.semester === 3 ? 'bg-secondary' : 'bg-info', history.semester_name) + '</td>' +
                        '<td>' + CissCrud.escapeHtml(history.course || '') + '</td>' +
                        '<td>' + CissCrud.escapeHtml(history.studYear) + '</td>' +
                        '<td>' + CissCrud.escapeHtml(history.studSec) + '</td>' +
                        '<td>' + CissCrud.escapeHtml(history.campus) + '</td>' +
                        '</tr>').join('')
                    : '<tr><td colspan="7" class="text-center text-muted">No enrollment history found.</td></tr>');
            })
            .fail(() => $rows.html('<tr><td colspan="7" class="text-center text-danger">Could not load the enrollment history.</td></tr>'));
    }

    $(document).on('submit', '#studentInfoSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });
})();
