{{--
    Pre-Enrollment Form PDF, rendered by AdmissionPreEnrollmentController with barryvdh/laravel-dompdf
    (Pdf::loadView(...)->stream()) and shown in the Pre-Enrollment modal's iframe.
    dompdf has limited CSS (no flex/grid, weak absolute positioning) and can't draw form inputs, so the
    layout uses tables and the checkboxes are ☑ / ☐ characters (DejaVu Sans has them).
    Program lists and the nurse signatory come from config('ciss.pre_enrollment').
--}}
@php
    $box = fn (bool $checked) => $checked ? '&#9745;' : '&#9744;';

    // Is this the course chosen in the interview? Compares the code, or the program name, ignoring
    // case, spaces and punctuation (ad_programs codes can be written a little differently from the config)
    $normalize = fn ($value) => preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $value));
    $isCourse = fn ($code, $label) => $course !== null && $course !== '' && (
        $normalize($code) === $normalize($course)
        || ($courseName && $normalize($label) === $normalize($courseName))
    );
    $type = (string) $applicant->type;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pre-Enrollment Form - {{ $applicant->admission_id }}</title>
    <style>
        @page { margin: 40px 48px; }
        body { font: 12px Arial, Helvetica, sans-serif; color: #000; }
        table { border-collapse: collapse; }
        .box { font-family: "DejaVu Sans", sans-serif; font-size: 13px; }
        .line { border-bottom: 1px solid #000; text-transform: uppercase; font-weight: bold; padding: 0 4px; }
        .caption { font-size: 10px; font-style: italic; text-align: center; padding-top: 2px; }
        .small { font-size: 10px; }
        .college { font-size: 10px; font-weight: bold; margin: 10px 0 2px 0; }
        .programs { font-size: 10px; margin: 0; padding: 0; list-style: none; }
        .programs li { margin: 1px 0; }
        .u { text-decoration: underline; }
    </style>
</head>
<body>
    {{-- Header: logo + school, photo box on the right --}}
    <table width="100%">
        <tr>
            <td width="70" valign="top">
                <img src="{{ public_path('uilibs/images/cpsulogov4.png') }}" width="60" alt="CPSU">
            </td>
            <td valign="top" style="padding-top: 8px;">
                <div style="font-size: 14px; font-weight: bold;">CENTRAL PHILIPPINES STATE UNIVERSITY</div>
                <div style="padding-left: 30px;">Kabankalan City, Negros Occidental</div>
            </td>
            <td width="130" align="right" valign="top">
                <div style="width: 130px; height: 120px; border: 1px solid #000;"></div>
            </td>
        </tr>
    </table>

    <div style="text-align: center; font-weight: bold; margin-top: 4px; margin-bottom: 18px;">PRE-ENROLLMENT FORM</div>

    {{-- Applicant type --}}
    <div style="text-align: center; font-weight: bold; margin-bottom: 18px;">
        <span class="box">{!! $box($type === '1') !!}</span> New
        &nbsp;&nbsp;&nbsp;
        <span class="box">{!! $box($type === '2') !!}</span> Returnee
        &nbsp;&nbsp;&nbsp;
        <span class="box">{!! $box($type === '3') !!}</span> Transferee
    </div>

    {{-- Name / Sex / Age --}}
    <table width="100%" style="font-size: 12px;">
        <tr>
            <td width="45">Name:</td>
            <td class="line" width="18%">{{ $applicant->lname }}</td>
            <td class="line" width="18%">{{ $applicant->fname }}</td>
            <td class="line" width="18%">{{ $applicant->mname }} {{ $applicant->ext && $applicant->ext !== 'N/A' ? $applicant->ext : '' }}</td>
            <td width="35" style="padding-left: 10px;">Sex:</td>
            <td class="line">{{ $applicant->gender }}</td>
            <td width="35" style="padding-left: 10px;">Age:</td>
            <td class="line" width="35">{{ $applicant->age }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="caption">Family Name</td>
            <td class="caption">Given Name</td>
            <td class="caption">Middle Name</td>
            <td colspan="2" style="padding: 6px 0 0 10px;">Date of Birth:</td>
            <td colspan="2" class="line" style="padding-top: 6px;">{{ $applicant->bday }}</td>
        </tr>
    </table>

    {{-- Address --}}
    <table width="100%" style="margin-top: 12px; font-size: 12px;">
        <tr>
            <td width="160">Complete Home Address:</td>
            <td class="line">{{ $applicant->address }}</td>
        </tr>
    </table>

    {{-- Admission test result --}}
    <table width="100%" style="margin-top: 16px; font-size: 12px;">
        <tr>
            <td width="150"><b>Admission Test Result:</b></td>
            <td>Test Date: <span class="u">{{ $applicant->d_admission }}</span></td>
            <td>Test Venue: <span class="u">{{ $applicant->venue }}</span></td>
        </tr>
        <tr>
            <td></td>
            <td style="padding-top: 4px;">Stanine: <span class="u">{{ $stanine }}</span></td>
            <td style="padding-top: 4px;">Remarks: <span class="u">{{ $remarks }}</span></td>
        </tr>
    </table>

    {{-- Signatories (third column: campus nurse) --}}
    <table width="100%" style="margin-top: 36px;" class="small">
        <tr>
            <td width="32%" class="line" style="text-align: center;">&nbsp;</td>
            <td width="2%"></td>
            <td width="32%" class="line" style="text-align: center;">&nbsp;</td>
            <td width="2%"></td>
            <td width="32%" class="line" style="text-align: center;">{{ $nurse['name'] ?? '' }}&nbsp;</td>
        </tr>
        <tr>
            <td></td><td></td><td></td><td></td>
            <td style="text-align: center; font-weight: bold; padding-top: 2px;">{{ $nurse['title'] }}</td>
        </tr>
    </table>

    {{-- Programs (the interviewed course is checked) --}}
    <div class="small" style="text-align: center; font-weight: bold; margin-top: 16px;">APPLICATION FOR ENROLLMENT TO:</div>

    @foreach ($colleges as $college => $programs)
        <div class="college">{{ $college }}</div>
        <ul class="programs">
            @foreach ($programs as $code => $label)
                <li><span class="box">{!! $box($isCourse($code, $label)) !!}</span> {{ $label }}</li>
            @endforeach
            @if (array_key_exists('BSED', $programs))
                <li style="margin-left: 20px;">
                    @foreach ($bsedMajors as $code => $label)
                        <span class="box">{!! $box($isCourse($code, $label)) !!}</span> {{ $label }}&nbsp;&nbsp;&nbsp;
                    @endforeach
                </li>
            @endif
        </ul>
    @endforeach

    {{-- Interview --}}
    <table width="100%" style="margin-top: 18px; font-size: 12px;">
        <tr>
            <td>Interviewed by: <span class="u">{{ $interview?->interviewer }}</span></td>
            <td>Rating: <span class="u">{{ $interview?->rating }}</span></td>
            <td>Date: <span class="u">&nbsp;</span></td>
        </tr>
    </table>

    {{-- Dean approval --}}
    <table width="100%" style="margin-top: 18px; font-size: 12px;">
        <tr>
            <td width="190"><b>APPROVED FOR ENROLLMENT:</b></td>
            <td style="border-bottom: 1px solid #000;">&nbsp;</td>
            <td width="110" style="padding-left: 10px;">Date: _________</td>
        </tr>
        <tr>
            <td></td>
            <td class="small" style="text-align: center; padding-top: 2px;">Name and Signature of the College Dean</td>
            <td></td>
        </tr>
    </table>

    <p class="small" style="text-align: center; margin-top: 24px;">
        Doc Control Code: CPSU-F-CGU-01&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Effective Date: 09/12/2018&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Page No.: 1 of 1
    </p>
</body>
</html>
