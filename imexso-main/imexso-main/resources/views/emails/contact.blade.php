<p><strong>New contact form submission</strong></p>
<p>
    <strong>Name:</strong> {{ $submission->first_name }} {{ $submission->last_name }}<br>
    <strong>Email:</strong> {{ $submission->email }}<br>
    <strong>Phone:</strong> {{ $submission->phone }}
</p>
<p><strong>Message:</strong></p>
<p>{!! nl2br(e($submission->message)) !!}</p>
