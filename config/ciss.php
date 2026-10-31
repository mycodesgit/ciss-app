<?php

/*
|--------------------------------------------------------------------------
| CISS lookups
|--------------------------------------------------------------------------
|
| Labels shared by models, components and views, so they are defined once.
|
*/

return [

    'campuses' => [
        'MC' => 'Main Campus',
        'VC' => 'Victorias Campus',
        'SCC' => 'San Carlos Campus',
        'MP' => 'Moises Padilla Campus',
        'HC' => 'Hinigaran Campus',
        'IC' => 'Ilog Campus',
        'CA' => 'Candoni Campus',
        'CC' => 'Cauayan Campus',
        'SC' => 'Sipalay Campus',
        'HinC' => 'Hinobaan Campus',
        'VE' => 'Valladolid Campus',
    ],

    // users.role => label (0 = Administrator, has access to every menu)
    'roles' => [
        0 => 'Administrator',
        1 => 'Guidance Officer',
        2 => 'Guidance Staff',
        3 => 'Registrar',
        4 => 'Registrar Staff',
        5 => 'College Dean',
        6 => 'Program Head',
        7 => 'College Staff',
        8 => 'Scholarship Head',
        9 => 'Scholarship Staff',
        10 => 'Assessment Head',
        11 => 'Assessment Staff',
        12 => 'MIS Staff',
        13 => 'MIS Director',
        14 => 'MIS Officer',
        15 => 'Graduate School Staff',
        16 => 'OSSA Staff',
        17 => 'Cashier',
        18 => 'Cashier Staff',
        19 => 'Encoder',
        20 => 'Dean of Instruction',
        21 => 'YearBook Staff',
    ],

    // Admission test: highest raw score => stanine (scores above the last bound are invalid)
    'stanines' => [12 => 1, 18 => 2, 23 => 3, 29 => 4, 36 => 5, 43 => 6, 49 => 7, 56 => 8, 72 => 9],

    // Stanine => remark saved in ad_examinee_result.percentile (campuses without a passing score below)
    'stanine_remarks' => [
        1 => 'Failed',
        2 => 'Failed',
        3 => 'Passed',
        4 => 'Passed',
        5 => 'Passed',
        6 => 'Passed',
        7 => 'Passed',
        8 => 'Passed',
        9 => 'Passed',
    ],

    // Campuses graded by a passing raw score instead of stanine: score >= value => 'Qualified', else 'Failed'
    'passing_scores' => [
        'MC' => 45,
    ],

    /*
    | Student ID made when an accepted applicant is pushed to enrollment: {year}-{0001}-{campus letter}.
    | Graduate School Staff (role 15) at Main Campus use their own letter.
    */
    'student_id_letters' => [
        'MC' => 'K',
        'VC' => 'V',
        'SCC' => 'R',
        'HC' => 'H',
        'MP' => 'M',
        'IC' => 'I',
        'CA' => 'A',
        'CC' => 'C',
        'SC' => 'P',
        'HinC' => 'N',
        'VE' => 'D',
    ],
    'graduate_school_letter' => 'G',

    /*
    | Student Report Card (PDF): the header image per campus (public/uilibs/images/reportcard),
    | the registrar who certifies it per campus (campuses not listed get an empty signature line),
    | and the NSTP subjects: their credits are shown in parentheses and left out of the average.
    */
    'report_card' => [
        'headers' => [
            'MC' => 'reportcardheaderMain.png',
            'VC' => 'reportcardheaderVic.jpg',
            'SCC' => 'reportcardheaderSCC.jpg',
            'HC' => 'reportcardheaderHC.jpg',
            'MP' => 'reportcardheaderMP.jpg',
            'IC' => 'reportcardheaderIC.jpg',
            'CC' => 'reportcardheaderCC.jpg',
            'CA' => 'reportcardheaderCA.jpg',
            'SC' => 'reportcardheaderSC.jpg',
            'HinC' => 'reportcardheaderHinC.jpg',
        ],
        'registrars' => [
            'MC' => ['name' => 'ELYSA JANE S. ARLOS', 'title' => 'OIC - University Registrar'],
            'VC' => ['name' => 'JUNO E. PAJARILLO, DPA', 'title' => 'Campus Registrar'],
            'SCC' => ['name' => 'JUJE C. RAMADA, LPT, Ph. D.', 'title' => 'Asst. Prof. / Campus Registrar'],
        ],
        'nstp_subjects' => [
            'KAB-SER-076', 'KAB-SER-077', 'KAB-SER-144', 'KAB-SER-145',
            'KAB-SER-146', 'KAB-SER-147', 'KAB-SER-148', 'KAB-SER-149',
        ],
    ],

    /*
    | NSTP components (NSTP module, Enrollment > CWTS/LTS/ROTC): key = URL slug, the label and the subject codes.
    | KAB-SER-076 / 077 are the old curriculum's NSTP 1 / NSTP 2 without a component.
    | Keep these in step with report_card.nstp_subjects.
    */
    'nstp_components' => [
        'cwts' => ['label' => 'CWTS', 'name' => 'Civic Welfare Training Service', 'codes' => ['KAB-SER-144', 'KAB-SER-147']],
        'lts' => ['label' => 'LTS', 'name' => 'Literacy Training Service', 'codes' => ['KAB-SER-145', 'KAB-SER-148']],
        'rotc' => ['label' => 'ROTC', 'name' => 'Reserve Officers Training Corps', 'codes' => ['KAB-SER-146', 'KAB-SER-149']],
        'nstp' => ['label' => 'NSTP 1/2', 'name' => 'NSTP 1/2 (old curriculum, no component)', 'codes' => ['KAB-SER-076', 'KAB-SER-077']],
    ],

    // NSTP student lists (CHED serial number format): the fixed HEI columns
    'nstp_hei' => [
        'name' => 'Central Philippines State University',
        'institutional_code' => '6058',
        'type' => 'SUC',
    ],

    /*
    | Pre-Enrollment Form (PDF): programs listed per college (the interviewed course is checked),
    | and the nurse signatory per campus (campuses not listed get an empty line titled "Nurse").
    */
    'pre_enrollment' => [
        'colleges' => [
            'COLLEGE OF AGRICULTURE and FORESTRY (CAF)' => [
                'BSAB' => 'Bachelor of Science in Agribusiness (BSAB)',
                'BSAGRI-As' => 'Bachelor of Science in Agriculture major in Animal Science',
                'BSAGRI-Cs' => 'Bachelor of Science in Agriculture major in Crop Science',
                'BSF' => 'Bachelor of Science in Forestry (BSF)',
                'BST' => 'Bachelor in Sugar Technology (BST)',
                'BS FISHERIES' => 'Bachelor of Science in Fisheries (BS FISHERIES)',
            ],
            'COLLEGE OF ARTS AND SCIENCES (CAS)' => [
                'BS-Stat' => 'Bachelor of Science in Statistics (BS Stat)',
                'ABEL' => 'Bachelor of Arts in English Language (ABEL)',
                'BASS' => 'Bachelor of Arts in Social Science (AB Soc Sci)',
            ],
            'COLLEGE OF BUSINESS AND MANAGEMENT (CBM)' => [
                'BSHM' => 'Bachelor of Science in Hospitality Management (BSHM)',
            ],
            'COLLEGE OF COMPUTER STUDIES (CCS)' => [
                'BSIT' => 'Bachelor of Science in Information Technology (BSIT)',
            ],
            'COLLEGE OF CRIMINAL JUSTICE EDUCATION (CCJE)' => [
                'BSCrim' => 'Bachelor of Science in Criminology (BSCrim)',
            ],
            'COLLEGE OF ENGINEERING (COE)' => [
                'BSABE' => 'Bachelor of Science in Agricultural and Biosystems Engineering (BSABE)',
                'BSEE' => 'Bachelor of Science in Electrical Engineering (BSEE)',
                'BSME' => 'Bachelor of Science in Mechanical Engineering (BSME)',
            ],
            'COLLEGE OF TEACHER EDUCATION (COTED)' => [
                'BECED' => 'Bachelor of Early Childhood Education (BECED)',
                'BEED-GE' => 'Bachelor of Elementary Education Major in General Education (BEED-GE)',
                'BPED' => 'Bachelor of Physical Education (BPED) major in Physical Education',
                'BSED' => 'Bachelor of Secondary Education major in:',
            ],
        ],
        // Shown on one line under BSED
        'bsed_majors' => [
            'BSED - English' => 'English',
            'BSED - Filipino' => 'Filipino',
            'BSED - Math' => 'Math',
            'BSED - Science' => 'Science',
        ],
        'nurses' => [
            'MC' => ['name' => 'KRISTINE C. DURAN, MAN', 'title' => 'Nurse II'],
        ],
    ],

    'departments' => [
        'CAS' => 'College of Arts and Sciences',
        'CCS' => 'College of Computer Studies',
        'CTE' => 'College of Teacher Education',
        'CCJE' => 'College of Criminal Justice Education',
        'COE' => 'College of Engineering',
        'CAF' => 'College of Agriculture and Forestry',
        'CBM' => 'College of Business Management',
        'Guidance Office' => 'Guidance Office',
        'Registrar Office' => 'Registrar Office',
        'Assessment Office' => 'Assessment Office',
        'Scholarship Office' => 'Scholarship Office',
        'Cashier Office' => 'Cashier Office',
        'Graduate School Registar' => 'Graduate School Registar',
        'MIS Office' => 'MIS Office',
        'OSSA' => 'OSSA',
    ],

    /*
    | Employee control menu. Keys match the values stored in button_access.buttons.
    | 'route' is the named route the item opens; null until that module exists.
    | 'always' items are shown enabled to every employee.
    | 'in_menu' => false: grantable in User Access but not shown as a menu button.
    */
    'employee_menu' => [
        'home-url' => ['label' => 'Home', 'icon' => 'fas fa-home', 'route' => 'employee.dashboard', 'always' => true],
        'admission-url' => ['label' => 'Admission', 'icon' => 'fas fa-id-card', 'route' => 'employee.admission.dashboard'],
        'enrollment-url' => ['label' => 'Enrollment', 'icon' => 'fas fa-laptop-code', 'route' => 'employee.enrollment.dashboard'],
        'scheduler-url' => ['label' => 'Scheduling', 'icon' => 'fas fa-calendar-alt', 'route' => 'employee.scheduling.dashboard'],
        'assessment-url' => ['label' => 'Assessment', 'icon' => 'fas fa-clipboard', 'route' => null],
        'cashiering-url' => ['label' => 'Cashier', 'icon' => 'fas fa-calculator', 'route' => null],
        'scholarship-url' => ['label' => 'Scholarship', 'icon' => 'fas fa-graduation-cap', 'route' => null],
        'yearbook-url' => ['label' => 'Yearbook', 'icon' => 'fas fa-book', 'route' => null],
        'grading-url' => ['label' => 'Grading', 'icon' => 'fas fa-star', 'route' => null, 'in_menu' => false],
        'kiosk-url' => ['label' => 'Kiosk', 'icon' => 'fas fa-desktop', 'route' => 'employee.kiosk.dashboard'],
        'queue-url' => ['label' => 'Queuing', 'icon' => 'fas fa-list-ol', 'route' => 'employee.queue.dashboard'],
        'nstp-url' => ['label' => 'Nstp', 'icon' => 'fas fa-person-rifle', 'route' => 'employee.nstp.dashboard'],
        'ossa-url' => ['label' => 'Ossa', 'icon' => 'fas fa-user-gear', 'route' => null],
        'request-url' => ['label' => 'Request', 'icon' => 'fas fa-file', 'route' => null],
        'setting-url' => ['label' => 'Accounts', 'icon' => 'fas fa-cog', 'route' => 'employee.settings.index'],
    ],

    /*
    | Scheduling module (menu key "scheduler-url").
    | pages: who may open each page (users.role list, and extra users.id list); the sidebar and the
    | 'scheduling:{page}' middleware both read it (User::canOpenSchedulingPage). The Dashboard is open to the module.
    */
    'scheduling' => [
        'pages' => [
            'colleges' => ['roles' => [0]],
            'programs' => ['roles' => [0]],
            'rooms' => ['roles' => [0, 12, 14]],
            'classes' => ['roles' => [0, 3, 12, 14, 15]],
            'curricula' => ['roles' => [0], 'users' => [24]],
            'designations' => ['roles' => [0, 12, 14]],
            'faculty' => ['roles' => [0, 5, 6, 7, 12, 14]],
            'offerings' => ['roles' => [0, 3, 12, 14, 15]],
        ],

        // college.id of the academic colleges (ADM and KAB are not listed)
        'college_ids' => [2, 3, 4, 5, 6, 7, 8],

        // Graduate School: program codes contain this, subject codes start with that (lecture fee typed by hand)
        'graduate_program_code' => '-GSS-',
        'graduate_subject_prefix' => 'KAB-GSS',
        // Subjects with codes starting with this are never offered
        'hidden_subject_prefix' => 'VIC',

        // Fees computed by the server (SubjectFees); NSTP subjects are config ciss.report_card.nstp_subjects
        'fees' => [
            'per_unit' => 180,
            'nstp_lecture' => 270,
            'laboratory' => 500,
            'ojt_development' => 3000,
            'thesis_laboratory' => 500,
        ],
        // assessment accountappraisal.id of the fund accounts a subject can be charged to
        'fund_accounts' => ['laboratory' => 85, 'nstp' => 105],

        'max_students' => 35,
        // Campuses that can add a section's subjects from its curriculum (Subject Offered > Template)
        'template_campuses' => ['MC'],
        // Optional text added after a new section's name (Classes Enrolled)
        'section_add_ons' => ['SIKAT - CAMI'],

        'offering_types' => [
            'No' => 'No', 'Special' => 'Special Class',
            '1st Cycle' => '1st Cycle', '2nd Cycle' => '2nd Cycle', '3rd Cycle' => '3rd Cycle', '4th Cycle' => '4th Cycle',
            'SHA 1st' => 'SHA 1st Cycle', 'SHA 2nd' => 'SHA 2nd Cycle', 'SHA 3rd' => 'SHA 3rd Cycle',
        ],

        // Faculty: majors (SED department only), suffixes, academic ranks
        'major_department' => 'SED',
        'majors' => ['English', 'Filipino', 'Math', 'Science'],
        'suffixes' => ['Ph.D.', 'Ed.D.', 'MIT', 'MSIT', 'MA', 'MBA', 'CPA', 'MEd'],
        'ranks' => [
            'Professor VI', 'Professor V', 'Professor IV', 'Professor III', 'Professor II', 'Professor I',
            'Associate Professor V', 'Associate Professor IV', 'Associate Professor III', 'Associate Professor II', 'Associate Professor I',
            'Assistant Professor IV', 'Assistant Professor III', 'Assistant Professor II', 'Assistant Professor I',
            'Instructor III', 'Instructor II', 'Instructor I', 'Part-Time',
        ],
        // addressee.id hidden from the Prefix list ("M...")
        'hidden_prefix_ids' => [4],

        // Faculty Designation: fac_designation.facCollege => label, and the designations per group (value => label)
        'designation_colleges' => [
            'ADM' => 'ADM', 'CAF' => 'CAF', 'CAS' => 'CAS', 'CBM' => 'CBM', 'CCS' => 'CCS', 'CJE' => 'CJE', 'COE' => 'COE', 'CTE' => 'COTED',
        ],
        'designations' => [
            'A. University Wide' => [
                'Dean of Instruction' => 'Dean of Instruction',
                'Vice President' => 'Vice President',
                'Dean' => 'Dean',
                'Quality Assurance' => 'Quality Assurance',
                'Research & Extension' => 'Research & Extension',
                'Planning Officer' => 'Planning Officer',
                'Director (by category)' => 'Director (by category)',
                'CampusAdmin' => 'Campus Administrator',
                'Coordinator' => 'Coordinator',
                'Internal Lead Auditor' => 'Internal Lead Auditor',
                'ISO/ISA Secretariat' => 'ISO/ISA Secretariat',
                'Faculty Regent' => 'Faculty Regent',
                'Editor-in-Chief/Managing Editor' => 'Editor-in-Chief/Managing Editor',
            ],
            'B. Campus/College Level' => [
                'Division Chair' => 'Division Chair',
                'Program Head' => 'Program Head',
                'Collecting and Disbursing Officer' => 'Collecting and Disbursing Officer',
                'Library Services/Librarian' => 'Library Services/Librarian',
                'Registrar' => 'Registrar',
                'Assessment' => 'Assessment',
                'MIS' => 'Network Administrator/Management Information System (MIS) In-charge',
                'Data Privacy Act Officer' => 'Data Privacy Act Officer',
                'Guidance and Counseling/Health Services' => 'Guidance and Counseling/Health Services',
                'Property Custodian/Supply Officer/Records Officer/Campus-HR' => 'Property Custodian/Supply Officer/Records Officer/Campus-HR',
                'OSSA Coordinator' => 'OSSA Coordinator',
                'NBC Local Evaluator' => 'NBC Local Evaluator',
                'Coordinator, Research/Extension' => 'Coordinator, Research/Extension',
                'Coordinator, Landscaping and Beautification' => 'Coordinator, Landscaping and Beautification',
                'Coordinator, Sports and Cultural' => 'Coordinator, Sports and Cultural',
                'Adviser, Student Organization (FLP/SSG)' => 'Adviser, Student Organization (FLP/SSG)',
                'Adviser, School Publication/Journal' => 'Adviser, School Publication/Journal',
                'Laboratory/AVR In-charge' => 'Laboratory/AVR In-charge',
                'Yearbook In-charge' => 'Yearbook In-charge',
                'Sports and Cultural Coach (Varsity)' => 'Sports and Cultural Coach (Varsity)',
                'Project In-charge' => 'Project In-charge',
                'Faculty with approved Research/Extension/Production proposal' => 'Faculty with approved Research/Extension/Production proposal',
            ],
        ],
    ],

];
