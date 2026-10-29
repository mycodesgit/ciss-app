<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accepted Applicants</title>
    <style>
        /* Paper size is set by the controller (Legal portrait). Top / bottom margins leave room for the header and footer images */
        @page {
            margin: 36mm 15mm 34mm 15mm;
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

        .page-header { top: -28mm; }
        .page-footer { bottom: -26mm; }

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
        .pushed { color: #1e7b34; font-weight: bold; }

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

    <div class="doc-title">List of Accepted Applicants</div>
    <div class="doc-subtitle">
        {{ $campusName }} &middot; Admission Year {{ $year }} &middot; <strong>{{ $strandName }}</strong>
    </div>

    <table class="list">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">#</th>
                <th style="width: 12%;">App ID</th>
                <th style="width: 26%;">Name</th>
                <th style="width: 10%;">Type</th>
                <th style="width: 13%;">Contact</th>
                <th style="width: 12%;">Course</th>
                <th style="width: 12%;">Status</th>
                <th>Strand</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($applicants as $applicant)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $applicant->admission_id }}</td>
                    <td>{{ $applicant->display_name }}</td>
                    <td>{{ \App\Models\AdmissionDB\Applicant::TYPES[$applicant->type] ?? '' }}</td>
                    <td>{{ $applicant->contact }}</td>
                    <td>{{ $applicant->deptRating?->course }}</td>
                    <td @class(['pushed' => (int) $applicant->p_status === \App\Models\AdmissionDB\Applicant::STATUS_ENROLLMENT])>{{ $statusLabel($applicant) }}</td>
                    <td>{{ $applicant->strand }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No accepted applicants for this search.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        Total: <strong>{{ $applicants->count() }}</strong> accepted applicant(s) &middot;
        Pushed to Enrollment: <strong>{{ $pushedCount }}</strong> &middot;
        Not yet pushed: <strong>{{ $applicants->count() - $pushedCount }}</strong>
    </div>
</body>
</html>
