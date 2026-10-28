/**
 * Admission application wizard (resources/views/pages/portal/apply.blade.php).
 *
 * This file is pushed to <head>, so with wire:navigate it is loaded only once
 * and stays loaded while the user moves between pages. Everything that touches
 * the page is therefore set up in init(), which Livewire calls on the first load
 * and after every navigation (livewire:navigated). teardown() stops timers and
 * window listeners before the user leaves the page.
 */
(function () {
    'use strict';

    let teardown = null;

    document.addEventListener('livewire:navigated', init);
    document.addEventListener('livewire:navigating', function () {
        if (teardown) {
            teardown();
            teardown = null;
        }
    });

    function init() {
        const form = document.getElementById('admissionApply');
        if (!form) return;

        const $form = $(form);
        const urls = form.dataset;
        const totalCards = form.querySelectorAll('[id^="card-"]').length;
        let currentCard = 1;
        let scheduleRefreshInterval = null;

        // ---------- Helpers ----------
        function urlWithId(template, id) {
            return template.replace('__ID__', encodeURIComponent(id));
        }

        function rowsFrom(data) {
            return Array.isArray(data) ? data : (data && Array.isArray(data.data) ? data.data : []);
        }

        function fillSelect($select, placeholder, rows, buildOption) {
            $select.html('<option disabled selected>' + placeholder + '</option>');
            rows.forEach(function (row) {
                $select.append(buildOption(row));
            });
        }

        function updateAddress() {
            const fullAddress = [
                $('#viewdatastudHnum').val(),
                $('#barangay').find(':selected').data('name'),
                $('#city').find(':selected').data('name'),
                $('#province').find(':selected').data('name'),
                $('#region').find(':selected').data('name'),
                $('#zipcode').val(),
            ].filter(Boolean).join(', ');

            $('#viewdatastudAddress').val(fullAddress);
        }

        function calculateAge() {
            const birthDate = new Date(document.getElementById('bday').value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();

            if (today.getMonth() < birthDate.getMonth() || (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())) {
                age--;
            }

            document.getElementById('age').value = isNaN(age) ? '' : age;
        }

        // An "Other" text box only gets the field's name while it is in use,
        // so it never overwrites the select's value with an empty string
        function toggleOtherInput(select, otherInput, fieldName) {
            if (select.value === 'OTHER') {
                otherInput.style.display = 'block';
                otherInput.setAttribute('name', fieldName);
                otherInput.required = true;
                otherInput.focus();
            } else {
                otherInput.style.display = 'none';
                otherInput.removeAttribute('name');
                otherInput.required = false;
                otherInput.value = '';
            }
        }

        function updateCoursePreferences(campus) {
            $.get(urls.programsUrl, { campus: campus })
                .done(function (data) {
                    ['preference_1', 'preference_2'].forEach(function (name) {
                        const $select = $form.find('select[name=' + name + ']');
                        $select.html('<option value="">Select Course Preference</option>');
                        data.programs.forEach(function (program) {
                            $select.append($('<option>').val(program.code).text(program.program));
                        });
                    });
                })
                .fail(function () {
                    console.error('Error fetching programs');
                });
        }

        function updateExamSchedule(campus) {
            $.get(urls.schedulesUrl, { campus: campus })
                .done(function (data) {
                    updateScheduleOptions(data.schedtest);
                })
                .fail(function () {
                    console.error('Error fetching Schedule Test');
                });
        }

        function updateScheduleOptions(schedules) {
            const $select = $form.find('select[name=d_admissionselect]');
            const currentSelectedId = $select.find('option:selected').data('primdatetimeid');
            let hasRestored = false;

            $select.html('<option disabled selected> --Select Testing Schedule-- </option>');

            schedules.forEach(function (schedule) {
                const label = moment(schedule.date).format('MMMM DD, YYYY') + ' ' + moment(schedule.time, 'HH:mm').format('hh:mm A');

                if (schedule.slots === 0) {
                    $select.append($('<option disabled>').text(label + ' (Slots is Full)'));
                    return;
                }

                const $option = $('<option>')
                    .val(schedule.date)
                    .attr('data-primdatetimeid', schedule.id)
                    .text(label + ' (Available Slots: ' + schedule.slots + ')');

                // Keep the user's choice selected while the list refreshes
                if (currentSelectedId && String(currentSelectedId) === String(schedule.id)) {
                    $option.prop('selected', true);
                    hasRestored = true;
                }

                $select.append($option);
            });

            if (hasRestored) {
                $select.trigger('change');
            }
        }

        function startScheduleRefresh() {
            stopScheduleRefresh();
            scheduleRefreshInterval = setInterval(function () {
                const campus = $('#campus').val();
                if (campus) updateExamSchedule(campus);
            }, 5000);
        }

        function stopScheduleRefresh() {
            if (scheduleRefreshInterval) clearInterval(scheduleRefreshInterval);
            scheduleRefreshInterval = null;
        }

        function toggleFormSections(type) {
            const newReturneeForm = document.getElementById('newReturneeForm');
            const transfereeForm = document.getElementById('transfereeForm');

            newReturneeForm.style.display = (type == 1 || type == 2) ? 'block' : 'none';
            transfereeForm.style.display = (type == 3) ? 'block' : 'none';
        }

        // ---------- Wizard ----------
        function currentCardFields() {
            return $('#card-' + currentCard).find('input, select');
        }

        function showCard(cardNumber) {
            document.getElementById('card-' + currentCard).style.display = 'none';
            document.getElementById('card-' + cardNumber).style.display = 'block';
            currentCard = cardNumber;
            updateProgressBar();
        }

        function nextCard() {
            if (currentCard >= totalCards) return;

            if (!currentCardFields().valid()) {
                validator.focusInvalid();
                return;
            }

            showCard(currentCard + 1);
        }

        function prevCard() {
            if (currentCard <= 1) return;
            showCard(currentCard - 1);
        }

        function toggleNextButton() {
            document.getElementById('next-btn').disabled = !currentCardFields().valid();
        }

        function updateProgressBar() {
            document.getElementById('progress-bar').style.width = (currentCard / totalCards) * 100 + '%';
            document.getElementById('progress-text').textContent = 'Page ' + currentCard + ' of ' + totalCards;

            document.getElementById('back-btn').style.display = currentCard > 1 ? 'inline-block' : 'none';
            document.getElementById('next-btn').style.display = currentCard < totalCards ? 'inline-block' : 'none';
            document.getElementById('submit-btn').style.display = currentCard === totalCards ? 'inline-block' : 'none';

            if (currentCard === totalCards) {
                checkLastCardCompletion();
            }
        }

        function checkLastCardCompletion() {
            const fields = form.querySelectorAll('#card-' + totalCards + ' input, #card-' + totalCards + ' select');
            const allFilled = Array.from(fields)
                .filter(function (field) { return field.offsetParent !== null; }) // only visible fields
                .every(function (field) { return field.value.trim() !== ''; });

            document.getElementById('submit-btn').disabled = !allFilled;
        }

        // ---------- Validation ----------
        const validator = $form.validate({
            rules: {
                // Card 1
                studagree: { required: true },
                email: {
                    required: true,
                    email: true,
                    pattern: /^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com)$/,
                },

                // Card 2
                lastname: { required: true },
                firstname: { required: true },
                gender: { required: true },
                bday: { required: true },
                age: { required: true },
                contact: { required: true, minlength: 11, maxlength: 11, digits: true },
                civil_status: { required: true },
                religion: { required: true },
                brgy: { required: true },
                city: { required: true },
                address: { required: true },
                qstion1: { required: true },
                qstion2: { required: true },

                // Card 3
                type: { required: true },
                campus: { required: true },
                d_admissionselect: { required: true },
                lstsch_attended: { required: true },
                strand: { required: true },
                suc_lst_attended: { required: true },
                course: { required: true },
                preference_1: { required: true },
                preference_2: { required: true },

                // Card 4
                monthly_income: { required: true },
                studiddoc_image: { required: true },
                typefileproofupload: { required: true },
                proofdoc_image: { required: true },
            },
            messages: {
                studagree: { required: 'Check the checkbox for agreement' },
                email: {
                    required: 'Please enter a email address',
                    email: 'Please enter a valid email address',
                    pattern: 'Please use only a popular email domain gmail.com',
                },
                lastname: { required: 'Enter Lastname' },
                firstname: { required: 'Enter Firstname' },
                gender: { required: 'Select Gender' },
                bday: { required: 'Enter Birthdate' },
                age: { required: 'Enter Age' },
                contact: {
                    required: 'Please enter your contact #.',
                    minlength: 'Contact number must be exactly 11 digits.',
                    maxlength: 'Contact number must be exactly 11 digits.',
                    digits: 'Please enter only digits.',
                },
                civil_status: { required: 'Select Status' },
                religion: { required: 'Enter Religion' },
                brgy: { required: 'Enter Barangay' },
                city: { required: 'Select City or Municipality' },
                address: { required: 'Enter Present Address' },
                qstion1: { required: 'Select Option' },
                qstion2: { required: 'Select Option' },
                type: { required: 'Select Admission Type' },
                campus: { required: 'Select Preferred Campus' },
                d_admissionselect: { required: 'Select Schedule of Examination' },
                lstsch_attended: { required: 'Enter Last School Attended' },
                strand: { required: 'Select Strand' },
                suc_lst_attended: { required: 'Enter College/University last attended' },
                course: { required: 'Select Course' },
                preference_1: { required: 'Select Preferred Course' },
                preference_2: { required: 'Select Preferred Course' },
                monthly_income: { required: "Enter Parent's Monthly Income" },
                studiddoc_image: { required: 'Upload one image from the requirements' },
                typefileproofupload: { required: 'Select Proof of Equity Target Documents from Parents/Legal Guardians' },
                proofdoc_image: { required: 'Upload one image from the requirements' },
            },
            onfocusout: false,
            onkeyup: false,
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.col-md-2, .col-md-6, .col-md-12').append(error);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            },
        });

        // ---------- Event bindings ----------
        $form.on('input change', 'input, select', toggleNextButton);
        $form.on('input change', '#card-' + totalCards + ' input, #card-' + totalCards + ' select', checkLastCardCompletion);

        $('#next-btn').on('click', nextCard);
        $('#back-btn').on('click', prevCard);
        $('#clear-form-btn').on('click', function () { form.reset(); });

        $('#bday').on('change', calculateAge);

        $('#religionSelect').on('change', function () {
            toggleOtherInput(this, document.getElementById('otherReligionInput'), 'religion');
        });

        $('#courseSelect').on('change', function () {
            toggleOtherInput(this, document.getElementById('otherCourseInput'), 'course');
        });

        $('.select2bs4').select2({ theme: 'bootstrap4' });

        // Region -> province -> city -> barangay cascade
        $('#region').on('change', function () {
            $('#region_name').val($(this).find(':selected').data('name'));
            updateAddress();

            $('#province').html('<option disabled selected>Loading...</option>');
            $.get(urlWithId(urls.provincesUrl, $(this).val()), function (data) {
                fillSelect($('#province'), 'Select Province', rowsFrom(data), function (p) {
                    return $('<option>').val(p.province_id).attr('data-name', p.name).text(p.name);
                });
            });
        });

        $('#province').on('change', function () {
            $('#province_name').val($(this).find(':selected').data('name'));
            updateAddress();

            $('#city').html('<option disabled selected>Loading...</option>');
            $.get(urlWithId(urls.citiesUrl, $(this).val()), function (data) {
                fillSelect($('#city'), 'Select City', rowsFrom(data), function (c) {
                    return $('<option>').val(c.city_id).attr('data-name', c.name).attr('data-zip', c.zip_code).text(c.name);
                });
            });
        });

        $('#city').on('change', function () {
            const $selected = $(this).find(':selected');
            $('#city_name').val($selected.data('name'));
            $('#zipcode').val($selected.data('zip') || '');
            updateAddress();

            $('#barangay').html('<option disabled selected>Loading...</option>');
            $.get(urlWithId(urls.barangaysUrl, $(this).val()), function (data) {
                fillSelect($('#barangay'), 'Select Barangay', rowsFrom(data), function (b) {
                    return $('<option>').val(b.id).attr('data-name', b.name).text(b.name);
                });
            });
        });

        $('#barangay').on('change', function () {
            $('#brgy_name').val($(this).find(':selected').data('name'));
            updateAddress();
        });

        $('#viewdatastudHnum').on('input', updateAddress);

        $('#email').on('blur', function () {
            document.getElementById('next-btn').style.display = $(this).val().trim() !== '' ? 'inline-block' : 'none';
        });

        // Campus -> course preferences + exam schedule (schedule refreshes every 5s while the tab is focused)
        $('#campus').on('change', function () {
            const campus = $(this).val();
            updateCoursePreferences(campus);
            updateExamSchedule(campus);
            if (campus) startScheduleRefresh(); else stopScheduleRefresh();
        });

        $(window).on('blur.admissionApply', stopScheduleRefresh);
        $(window).on('focus.admissionApply', function () {
            const campus = $('#campus').val();
            if (campus) {
                updateExamSchedule(campus);
                startScheduleRefresh();
            }
        });

        $form.find('select[name="d_admissionselect"]').on('change', function () {
            const $selected = $(this).find('option:selected');
            const dateTime = moment($selected.text(), 'MMMM D, YYYY hh:mm A');

            $('#selectedDate').val(dateTime.format('YYYY-MM-DD'));
            $('#selectedTime').val(dateTime.format('HH:mm:ss'));
            $('#selectedDateTimeID').val($selected.data('primdatetimeid'));
        });

        $('#genderSelect').on('change', function () {
            const gender = this.value;
            const genderQuestion = document.getElementById('genderQuestion');

            if (gender === 'Female' || gender === 'Male') {
                document.getElementById('genderPlaceholder').textContent = gender.toUpperCase();
                genderQuestion.style.display = 'block';
            } else {
                genderQuestion.style.display = 'none';
            }
        });

        const admissionType = document.getElementById('admissionType');
        toggleFormSections(admissionType.value);
        $(admissionType).on('change', function () {
            toggleFormSections(this.value);
        });

        updateProgressBar();

        teardown = function () {
            stopScheduleRefresh();
            $(window).off('.admissionApply');
        };
    }
})();
