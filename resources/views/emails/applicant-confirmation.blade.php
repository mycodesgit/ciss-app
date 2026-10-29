<p>Dear {{ $data['applicant_name'] }},</p>

<p>Your application has been approved and you are now scheduled for the Admission Test:</p>

<ul>
    <li><strong>Date:</strong> {{ $data['date'] }}</li>
    <li><strong>Time:</strong> {{ $data['time'] }}</li>
    <li><strong>Venue:</strong> {{ $data['venue'] }}</li>
</ul>

<p>Please arrive at least 30 minutes early and bring a valid ID.</p>

<p>This is a system-generated message. Please do not reply.</p>
