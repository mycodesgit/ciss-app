<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        /* Paper size is set by the controller (A4 portrait). Top / bottom margins leave room for the header and footer images */
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

        .summary {
            margin-top: 8px;
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

    <div class="doc-title">List of {{ $title }}</div>
    <div class="doc-subtitle">
        {{ $campusName }} &middot; <strong>{{ $term->label }}</strong>
    </div>

    <table class="list">
        <thead>
            <tr>
                <th class="text-center" style="width: 8%;">#</th>
                <th style="width: 22%;">Student ID</th>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $student->studentID }}</td>
                    <td>
                        {{ $student->lname }}, {{ $student->fname }}
                        {{ $student->mname ? mb_substr($student->mname, 0, 1) . '.' : '' }}
                        {{ $student->ext && $student->ext !== 'N/A' ? $student->ext : '' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        Total: <strong>{{ $students->count() }}</strong> student(s)
    </div>
</body>
</html>
