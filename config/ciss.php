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

    /*
    | Employee control menu. Keys match the values stored in button_access.buttons.
    | 'route' is the named route the item opens; null until that module exists.
    | 'always' items are shown enabled to every employee.
    */
    'employee_menu' => [
        'home-url' => ['label' => 'Home', 'icon' => 'fas fa-home', 'route' => 'employee.dashboard', 'always' => true],
        'admission-url' => ['label' => 'Admission', 'icon' => 'fas fa-id-card', 'route' => null],
        'enrollment-url' => ['label' => 'Enrollment', 'icon' => 'fas fa-laptop-code', 'route' => null],
        'scheduler-url' => ['label' => 'Scheduling', 'icon' => 'fas fa-calendar-alt', 'route' => null],
        'assessment-url' => ['label' => 'Assessment', 'icon' => 'fas fa-clipboard', 'route' => null],
        'cashiering-url' => ['label' => 'Cashier', 'icon' => 'fas fa-calculator', 'route' => null],
        'scholarship-url' => ['label' => 'Scholarship', 'icon' => 'fas fa-graduation-cap', 'route' => null],
        'yearbook-url' => ['label' => 'Yearbook', 'icon' => 'fas fa-book', 'route' => null],
        'kiosk-url' => ['label' => 'Kiosk', 'icon' => 'fas fa-desktop', 'route' => null],
        'queue-url' => ['label' => 'Queuing', 'icon' => 'fas fa-list-ol', 'route' => null],
        'nstp-url' => ['label' => 'Nstp', 'icon' => 'fas fa-person-rifle', 'route' => null],
        'ossa-url' => ['label' => 'Ossa', 'icon' => 'fas fa-user-gear', 'route' => null],
        'request-url' => ['label' => 'Request', 'icon' => 'fas fa-file', 'route' => null],
        'setting-url' => ['label' => 'Accounts', 'icon' => 'fas fa-cog', 'route' => 'employee.settings.index'],
    ],

];
