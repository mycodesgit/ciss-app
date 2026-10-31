<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    {{-- Kiosk > Bulk Generation: the new kiosk passwords of a class, printed once (only their hashes are saved) --}}
    <title>Kiosk Passwords - {{ $title }}</title>
    <style>
        /* Paper size is set by the controller (Legal portrait). Top / bottom margins leave room for the header and footer images */
        @page {
            margin: 34mm 15mm 32mm 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9.5pt;
            color: #000;
            margin: 0;
        }

        /* Header and footer images on every page (fixed = drawn in the page margins) */
        .page-header,
        .page-footer {
            position: fixed;
            left: 0;
            right: 0;
        }

        .page-header { top: -27mm; }
        .page-footer { bottom: -25mm; }

        .page-header img,
        .page-footer img {
            width: 100%;
            display: block;
        }

        .doc-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .doc-subtitle {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 12px;
        }

        .group-title {
            font-size: 10.5pt;
            font-weight: bold;
            margin: 12px 0 4px;
        }

        table.list {
            width: 100%;
            border-collapse: collapse;
        }

        table.list th,
        table.list td {
            border: 1px solid #000;
            padding: 4px 5px;
            vertical-align: top;
        }

        table.list th {
            background-color: #d9ead3;
            font-size: 9pt;
            text-align: left;
        }

        /* Repeat the column titles on every page */
        table.list thead { display: table-header-group; }
        table.list tr { page-break-inside: avoid; }

        .text-center { text-align: center; }
        .password { font-family: 'Courier New', monospace; font-size: 11pt; font-weight: bold; letter-spacing: 1px; }

        .summary {
            margin-top: 10px;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <!-- Fixed elements go first so dompdf repeats them on every page -->
    <div class="page-header">
        <img src="{{ public_path('uilibs/images/allformheader/header.png') }}" alt="">
    </div>
    <div class="page-footer">
        <img src="{{ public_path('uilibs/images/allformheader/footer.png') }}" alt="">
    </div>

    <div class="doc-title">Student Kiosk Passwords</div>
    <div class="doc-subtitle">
        {{ $title }} &middot; {{ $campusName }} &middot; <strong>{{ $term->label }}</strong>
    </div>

    {{-- Males, then females; a group with no students is left out --}}
    @foreach ($groups as $groupTitle => $students)
        @continue($students->isEmpty())

        <div class="group-title">{{ $groupTitle }} ({{ $students->count() }})</div>
        <table class="list">
            <thead>
                <tr>
                    <th class="text-center" style="width: 8%;">#</th>
                    <th style="width: 22%;">Student ID</th>
                    <th>Name</th>
                    <th style="width: 20%;">Password</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $student->stud_id }}</td>
                        <td>{{ $student->full_name }}</td>
                        <td class="password">{{ $student->kiosk_password }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="summary">
        Total: <strong>{{ $total }}</strong> student(s). These passwords are shown only on this printout; a lost one is reset in Kiosk &gt; Kiosk User.
    </div>
</body>
</html>
