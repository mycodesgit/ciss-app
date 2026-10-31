<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    {{-- Enrollment > Student Record: a student's whole academic record (StudentRecordController::pdf) --}}
    <title>Student Record - {{ $student->stud_id }}</title>
    <style>
        @page { margin: 12mm 15mm 15mm 15mm; }

        body { font-family: Arial, sans-serif; font-size: 9.5pt; color: #000; margin: 0; }

        .header { text-align: center; margin-bottom: 8px; }
        .header img { width: 80%; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px 3px; text-align: left; vertical-align: top; }

        .info td { font-size: 10pt; }
        .info .label { white-space: nowrap; width: 1%; padding-right: 6px; }

        .section-title { text-align: center; font-weight: bold; font-size: 10pt; margin: 8px 0 4px; }

        .grades { margin-top: 10px; }
        .grades thead th { border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 9.5pt; }
        /* Repeat the column titles on every page */
        .grades thead { display: table-header-group; }
        .grades tr { page-break-inside: avoid; }
        .term td { text-align: center; font-weight: bold; padding-top: 10px; }
        .num { text-align: center; }
        .red { color: #c00; }

        .footer { margin-top: 14px; border-top: 1px solid #000; padding-top: 4px; font-size: 8.5pt; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('uilibs/images/studentrecord/studentrecordmain.jpg') }}" alt="">
    </div>

    <table class="info">
        <tr>
            <td class="label">Name:</td>
            <td><strong>{{ $name }}</strong> ({{ $student->stud_id }})</td>
            <td class="label">Date:</td>
            <td>{{ now()->format('F j, Y') }}</td>
        </tr>
        <tr>
            <td class="label">Date of Birth:</td>
            <td>{{ $student->bday ? \Carbon\Carbon::parse($student->bday)->format('F j, Y') : 'N/A' }}</td>
            <td class="label">Place of Birth:</td>
            <td>{{ $student->pbirth ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Address:</td>
            <td colspan="3">{{ $address ?: 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">OTHER PRELIMINARY</div>
    <table class="info">
        <tr>
            <td class="label">Date of Admission:</td>
            <td>{{ $student->date_admission ? \Carbon\Carbon::parse($student->date_admission)->format('F j, Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Degree/Curriculum:</td>
            <td>{{ $program ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Major:</td>
            <td>{{ $major ?: 'N/A' }}</td>
        </tr>
    </table>

    {{-- N.Eqv. = the grade's GPA equivalent, R.Eqv. = the grade as encoded; NSTP credits in parentheses --}}
    <table class="grades">
        <thead>
            <tr>
                <th style="width: 18%;">Subject Code</th>
                <th>Descriptive Title</th>
                <th class="num" style="width: 9%;">N.Eqv.</th>
                <th class="num" style="width: 9%;">R.Eqv.</th>
                <th class="num" style="width: 9%;">Comp.</th>
                <th class="num" style="width: 9%;">Credits</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($terms as $term)
                <tr class="term"><td colspan="6">{{ $term['label'] }}</td></tr>
                @foreach ($term['rows'] as $row)
                    <tr>
                        <td>{{ $row['subject'] }}</td>
                        <td>{{ $row['title'] }}</td>
                        <td class="num {{ $row['gradeWarning'] ? 'red' : '' }}">{{ $row['grade'] }}</td>
                        <td class="num">{{ $row['rawGrade'] }}</td>
                        <td class="num">{{ $row['completion'] }}</td>
                        <td class="num">{{ $row['nstp'] ? '(' . $row['credit'] . ')' : $row['credit'] }}</td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="6" class="num" style="padding: 12px;">No grades recorded.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Total credits earned: <strong>{{ $terms->sum('credits') }}</strong>
        &middot; Printed {{ now()->format('F j, Y h:i A') }}
    </div>
</body>
</html>
