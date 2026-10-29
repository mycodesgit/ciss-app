<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Slip</title>
    <style>
        /* Paper size is set by the controller (A4 portrait) */
        /* The bottom margin leaves room for the footer image (about 20mm high at full width) */
        @page {
            margin: 12mm 15mm 34mm 15mm;
        }

        /* Footer image at the bottom of every page (fixed = drawn in the page's bottom margin) */
        .page-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -26mm;
        }

        .page-footer img {
            width: 100%;
            display: block;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        /* Helper table styles for dompdf rendering */
        table.layout-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        table.layout-table td {
            padding: 0;
            vertical-align: top;
        }

        /* Fixed alignment class for field labels */
        .field-label {
            vertical-align: top !important;
            padding-top: 2px;
        }

        .doc-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        /* Photo Frame Top Right */
        .photo-box {
            width: 125px;
            height: 125px;
            border: 1px solid #000;
            text-align: center;
        }

        .photo-placeholder-text {
            padding-top: 55px;
            font-size: 8pt;
            color: #777;
        }

        .photo-caption {
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-top: 3px;
        }

        /* Form Underlines & Sub-captions */
        .line-value {
            border-bottom: 1px solid #000;
            text-align: center;
            font-size: 9.5pt;
            min-height: 15px;
            text-transform: uppercase;
        }

        .sub-caption {
            font-size: 7.5pt;
            text-align: center;
            font-style: italic;
            margin-top: 1px;
        }

        .section-heading {
            font-size: 9.5pt;
            text-decoration: underline;
            margin-top: 10px;
            margin-bottom: 8px;
        }

        /* Signatures */
        .sig-container {
            margin-top: 30px;
            text-align: center;
        }

        .sig-line {
            border-top: 1px solid #000;
            font-size: 8pt;
            font-style: italic;
            padding-top: 2px;
        }

        .admin-sig-name {
            font-size: 9.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .admin-sig-title {
            font-size: 8pt;
            font-style: italic;
        }
    </style>
</head>
<body>
    @php
        $middleInitial = $applicant->mname ? mb_substr($applicant->mname, 0, 1) . '.' : '';
        $printedName = trim($applicant->fname . ' ' . $middleInitial . ' ' . $applicant->lname . ' ' . $applicant->ext);

        // Exam schedule (empty until the applicant is scheduled)
        $examDate = $applicant->d_admission ? \Carbon\Carbon::parse($applicant->d_admission)->format('M d, Y') : '';
        $examTime = $applicant->time ? \Carbon\Carbon::parse($applicant->time)->format('h:i A') : '';

        // Age and date of birth from the birthdate
        $dob = $applicant->bday ? \Carbon\Carbon::parse($applicant->bday) : null;
        $age = $dob ? $dob->age : '';
        $birthMonth = $dob ? strtoupper($dob->format('F')) : '';
        $birthDay = $dob ? $dob->format('d') : '';
        $birthYear = $dob ? $dob->format('Y') : '';
    @endphp

    <!-- Footer: fixed elements go first so dompdf repeats them on every page -->
    <div class="page-footer">
        <img src="{{ public_path('uilibs/images/allformheader/footer.png') }}" alt="">
    </div>

    <!-- Header Area: full page width -->
    <img src="{{ public_path('uilibs/images/allformheader/headerAdSlip.png') }}" alt="" style="width: 100%; display: block; margin-bottom: 8px;">

    <!-- ID Photo: below the header, on the right -->
    <table class="layout-table" style="margin-bottom: 5px;">
        <tr>
            <td>&nbsp;</td>
            <td style="width: 130px; text-align: right;">
                <div class="photo-box">
                    <div class="photo-placeholder-text">ID Photo</div>
                </div>
                <div class="photo-caption">{{ $printedName }}</div>
            </td>
        </tr>
    </table>

    <!-- Document Title -->
    <div class="doc-title">Admission Slip</div>

    <!-- Date & Examinee No -->
    <table class="layout-table" style="margin-bottom: 16px;">
        <tr>
            <td style="width: 38px;" class="field-label">Date:</td>
            <td style="width: 100px;" class="line-value">{{ $examDate }}</td>
            <td>&nbsp;</td>
            <td style="width: 90px; text-align: right; padding-right: 5px;" class="field-label">Examinee No.</td>
            <td style="width: 140px;" class="line-value">
                <span style="font-size: 12pt; font-weight: bold;">{{ $applicant->admission_id }}</span>
            </td>
        </tr>
    </table>

    <!-- Name Row -->
    <table class="layout-table" style="margin-bottom: 12px;">
        <tr>
            <td style="width: 50px;" class="field-label">Name:</td>
            <td style="padding-right: 10px;">
                <div class="line-value">{{ trim($applicant->lname . ' ' . $applicant->ext) }}</div>
                <div class="sub-caption">Last Name</div>
            </td>
            <td style="padding-right: 10px;">
                <div class="line-value">{{ $applicant->fname }}</div>
                <div class="sub-caption">First Name</div>
            </td>
            <td>
                <div class="line-value">{{ $applicant->mname }}</div>
                <div class="sub-caption">Middle Name</div>
            </td>
        </tr>
    </table>

    <!-- Address Row -->
    <table class="layout-table" style="margin-bottom: 12px;">
        <tr>
            <td style="width: 60px;" class="field-label">Address:</td>
            <td style="padding-right: 10px;">
                <div class="line-value">{{ $applicant->brgy }}</div>
                <div class="sub-caption">Barangay</div>
            </td>
            <td style="padding-right: 10px;">
                <div class="line-value">{{ $applicant->city }}</div>
                <div class="sub-caption">City/Municipality</div>
            </td>
            <td>
                <div class="line-value">{{ $applicant->province }}</div>
                <div class="sub-caption">Province</div>
            </td>
        </tr>
    </table>

    <!-- Sex, Age, DOB Row -->
    <table class="layout-table" style="margin-bottom: 12px;">
        <tr>
            <td style="width: 32px;" class="field-label">Sex:</td>
            <td style="width: 80px; padding-right: 15px;">
                <div class="line-value">{{ strtoupper($applicant->gender ?? '') }}</div>
            </td>
            <td style="width: 32px;" class="field-label">Age:</td>
            <td style="width: 50px; padding-right: 15px;">
                <div class="line-value">{{ $age }}</div>
            </td>
            <td style="width: 85px;" class="field-label">Date of Birth:</td>
            <td style="width: 80px; padding-right: 5px;">
                <div class="line-value">{{ $birthMonth }}</div>
                <div class="sub-caption">Month</div>
            </td>
            <td style="width: 40px; padding-right: 5px;">
                <div class="line-value">{{ $birthDay }}</div>
                <div class="sub-caption">Day</div>
            </td>
            <td style="width: 60px;">
                <div class="line-value">{{ $birthYear }}</div>
                <div class="sub-caption">Year</div>
            </td>
        </tr>
    </table>

    <!-- Contact & Email Row -->
    <table class="layout-table" style="margin-bottom: 16px;">
        <tr>
            <td style="width: 105px;" class="field-label">Contact Number:</td>
            <td style="padding-right: 15px;">
                <div class="line-value">{{ $applicant->contact }}</div>
            </td>
            <td style="width: 95px;" class="field-label">E-mail Address:</td>
            <td>
                <div class="line-value" style="text-transform: lowercase;">{{ $applicant->email }}</div>
            </td>
        </tr>
    </table>

    <!-- For New Student -->
    <div class="section-heading">For New Student</div>
    <table class="layout-table" style="margin-bottom: 8px;">
        <tr>
            <td style="width: 135px;" class="field-label">School Last Attended:</td>
            <td><div class="line-value">{{ $applicant->lstsch_attended }}</div></td>
        </tr>
    </table>
    <table class="layout-table" style="margin-bottom: 16px;">
        <tr>
            <td style="width: 195px;" class="field-label">Senior High School Track/Strand:</td>
            <td><div class="line-value">{{ $applicant->strand }}</div></td>
        </tr>
    </table>

    <!-- For Transferee -->
    <div class="section-heading">For Transferee</div>
    <table class="layout-table" style="margin-bottom: 8px;">
        <tr>
            <td style="width: 135px;" class="field-label">School Last Attended:</td>
            <td><div class="line-value" style="font-size: 8.5pt;">{{ $applicant->suc_lst_attended }}</div></td>
        </tr>
    </table>
    <table class="layout-table" style="margin-bottom: 16px;">
        <tr>
            <td style="width: 100px;" class="field-label">Course & Year:</td>
            <td><div class="line-value">{{ $applicant->course }}</div></td>
        </tr>
    </table>

    <!-- Course Choices -->
    <table class="layout-table" style="margin-bottom: 25px;">
        <tr>
            <td style="width: 105px;" class="field-label">Course Choice 1:</td>
            <td style="padding-right: 15px;">
                <div class="line-value" style="font-size: 8.5pt;">{{ $applicant->preference_1 }}</div>
            </td>
            <td style="width: 105px;" class="field-label">Course Choice 2:</td>
            <td>
                <div class="line-value" style="font-size: 8.5pt;">{{ $applicant->preference_2 }}</div>
            </td>
        </tr>
    </table>

    <!-- Student Signature -->
    <table class="layout-table" style="margin-bottom: 25px;">
        <tr>
            <td>&nbsp;</td>
            <td style="width: 220px; text-align: center;">
                <div class="line-value" style="border: none; margin-bottom: 15px;">{{ $printedName }}</div>
                <div class="sig-line">Signature over Printed Name</div>
            </td>
        </tr>
    </table>

    <!-- Exam Details Row -->
    <table class="layout-table" style="margin-bottom: 30px;">
        <tr>
            <td style="width: 145px;" class="field-label">Date of Admission Test:</td>
            <td style="width: 100px; padding-right: 15px;">
                <div class="line-value">{{ $examDate }}</div>
            </td>
            <td style="width: 40px;" class="field-label">Time:</td>
            <td style="width: 80px; padding-right: 15px;">
                <div class="line-value">{{ $examTime }}</div>
            </td>
            <td style="width: 48px;" class="field-label">Venue:</td>
            <td>
                <div class="line-value" style="font-size: 8.5pt; text-transform: none;">{{ $applicant->venue }}</div>
            </td>
        </tr>
    </table>

    <!-- University Psychometrician Signature -->
    <div class="sig-container">
        <div class="admin-sig-name">SUNE S. QUINTAB, JD, CHRA.</div>
        <div class="admin-sig-title">OIC Director SAFE CENTER</div>
    </div>

</body>
</html>
