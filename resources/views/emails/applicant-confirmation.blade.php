{{--
    Sent by ApplicantConfirmationMail when an applicant is pushed to the examinee list.
    Email clients ignore <style> blocks and most modern CSS, so the layout uses tables and inline styles.
    The logo is embedded in the message (works even when the app is not publicly reachable).
--}}
@php
    $green = '#04401f';
    $logo = $message->embed(public_path('uilibs/images/cpsulogov4.png'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Test Schedule</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f4f2; font-family: Arial, Helvetica, sans-serif; color: #333333;">
    {{-- Preview text shown next to the subject in the inbox --}}
    <div style="display: none; max-height: 0; overflow: hidden;">
        Your Admission Test is on {{ $data['date'] }} at {{ $data['time'] }}, {{ $data['venue'] }}.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f4f2; padding: 24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);">

                    {{-- Header --}}
                    <tr>
                        <td align="center" style="background-color: {{ $green }}; padding: 28px 24px;">
                            <img src="{{ $logo }}" alt="CPSU" width="72" height="72" style="display: block; border: 0; margin-bottom: 12px;">
                            <div style="color: #ffffff; font-size: 18px; font-weight: bold; letter-spacing: 0.5px;">Central Philippines State University</div>
                            <div style="color: #cfe3d6; font-size: 13px; margin-top: 4px;">Admission Office</div>
                        </td>
                    </tr>

                    {{-- Status banner --}}
                    <tr>
                        <td align="center" style="background-color: #e6f2ea; padding: 14px 24px; color: {{ $green }}; font-size: 15px; font-weight: bold;">
                            &#10004; Your application has been approved
                        </td>
                    </tr>

                    {{-- Message --}}
                    <tr>
                        <td style="padding: 28px 32px 8px 32px; font-size: 15px; line-height: 1.6;">
                            <p style="margin: 0 0 14px 0;">Dear <strong>{{ $data['applicant_name'] }}</strong>,</p>
                            <p style="margin: 0;">
                                Congratulations! You are now scheduled to take the <strong>CPSU Admission Test</strong>.
                                Please take note of your schedule below.
                            </p>
                        </td>
                    </tr>

                    {{-- Schedule card --}}
                    <tr>
                        <td style="padding: 20px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #d5e5da; border-left: 5px solid {{ $green }}; border-radius: 6px; background-color: #f8fbf9;">
                                <tr>
                                    <td style="padding: 18px 20px;">
                                        <div style="font-size: 12px; font-weight: bold; color: {{ $green }}; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">
                                            Admission Test Schedule
                                        </div>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size: 15px;">
                                            @foreach ([
                                                'Admission ID' => $data['admission_id'] ?? null,
                                                'Date' => $data['date'],
                                                'Time' => $data['time'],
                                                'Venue' => $data['venue'],
                                                'Campus' => $data['campus'] ?? null,
                                            ] as $label => $value)
                                                @if ($value)
                                                    <tr>
                                                        <td width="120" style="padding: 6px 0; color: #6b7280; vertical-align: top;">{{ $label }}</td>
                                                        <td style="padding: 6px 0; font-weight: bold; color: #111827;">{{ $value }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Reminders --}}
                    <tr>
                        <td style="padding: 4px 32px 24px 32px; font-size: 14px; line-height: 1.6;">
                            <div style="font-weight: bold; color: {{ $green }}; margin-bottom: 6px;">Reminders on the test day</div>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="font-size: 14px;">
                                @foreach ([
                                    'Arrive at least <strong>30 minutes</strong> before your scheduled time.',
                                    'Bring a <strong>valid ID</strong> and your <strong>Admission ID</strong>.',
                                    'Wear appropriate attire and observe the campus rules.',
                                ] as $reminder)
                                    <tr>
                                        <td style="padding: 3px 10px 3px 0; color: {{ $green }}; vertical-align: top;">&#8226;</td>
                                        <td style="padding: 3px 0;">{!! $reminder !!}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 32px 28px 32px; font-size: 14px; line-height: 1.6;">
                            <p style="margin: 0;">We wish you the best of luck on your Admission Test!</p>
                            <p style="margin: 14px 0 0 0;">Sincerely,<br><strong>CPSU Admission Office</strong></p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="background-color: #f7f7f7; border-top: 1px solid #e5e7eb; padding: 18px 24px; font-size: 12px; color: #6b7280; line-height: 1.5;">
                            This is a system-generated message from CISS. Please do not reply to this email.<br>
                            CPSU Integrated System Solution &middot; Management Information System Office (MISO)
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
