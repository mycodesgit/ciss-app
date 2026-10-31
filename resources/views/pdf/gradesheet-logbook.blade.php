<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gradesheet Logbook - {{ $college }}</title>
    <style>
        /* Paper size is set by the controller (Legal landscape). Top / bottom margins leave room for the header and footer images */
        @page {
            margin: 34mm 15mm 30mm 15mm;
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
            text-align: center;
        }

        .page-header { top: -28mm; }
        .page-footer { bottom: -24mm; }

        .page-header img,
        .page-footer img {
            width: 60%;
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

        table.list {
            width: 100%;
            border-collapse: collapse;
        }

        table.list th,
        table.list td {
            border: 1px solid #000;
            padding: 4px 5px;
            height: 22px;
            vertical-align: middle;
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

    <div class="doc-title">Gradesheet Logbook</div>
    <div class="doc-subtitle">
        {{ $college }} &middot; {{ $campusName }} &middot; <strong>{{ $term->label }}</strong>
    </div>

    <table class="list">
        <thead>
            <tr>
                <th class="text-center" style="width: 4%;">#</th>
                <th style="width: 18%;">Faculty</th>
                <th style="width: 11%;">Subject</th>
                <th>Subject Title</th>
                <th style="width: 12%;">Curr/Yr/Sec</th>
                <th style="width: 10%;">Date Submitted</th>
                <th class="text-center" style="width: 15%;">Signature</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($classes as $class)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $class->faculty }}</td>
                    <td>{{ $class->subject }}</td>
                    <td>{{ $class->title }}</td>
                    <td>{{ $class->section }}</td>
                    <td>{{ $class->submitted }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No classes found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        Total: <strong>{{ $classes->count() }}</strong> class(es) &middot;
        Submitted: <strong>{{ $classes->whereNotNull('submitted')->count() }}</strong>
    </div>
</body>
</html>
