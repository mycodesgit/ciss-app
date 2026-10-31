/**
 * Course select of the term searches (EL and PL, Ranking): <form data-courses-url="..."> with campus, schlyear,
 * semester and progCod selects. Choosing a campus, school year and semester loads the courses (SearchesCourses::courses).
 * The listener is on document, so it works after wire:navigate.
 */
(function () {
    'use strict';

    $(document).on('change', 'form[data-courses-url] select[name="campus"], form[data-courses-url] select[name="schlyear"], form[data-courses-url] select[name="semester"]', function () {
        const form = this.form;
        const elements = form.elements;
        const $course = $(elements.progCod);
        if (!elements.semester.value) return;

        $course.html('<option value="" selected disabled>Loading...</option>');
        $.get(form.dataset.coursesUrl, { campus: elements.campus.value, schlyear: elements.schlyear.value, semester: elements.semester.value })
            .done(function (response) {
                $course.html('<option value="" selected disabled>' + (response.data.length ? 'Select a course' : 'No courses in this term') + '</option>' +
                    response.data.map((course) => '<option value="' + CissCrud.escapeHtml(course.progCod) + '">' + CissCrud.escapeHtml(course.progAcronym) + '</option>').join(''));
            })
            .fail(function () {
                $course.html('<option value="" selected disabled>Select a semester first</option>');
                toastr.error('Could not load the courses.');
            });
    });
})();
