<!DOCTYPE html>
<html>
<head>
    <title>{{ $details['subject'] }}</title>
</head>
<body>
    <h2>Hi there, I'm, {{ $details['name'] }}!</h2>
    <p>Please have a look on my resume. </p>
    <p>{{ $details['message'] }}</p>

    <p>
        @if($details['attachment'])
        <a href="{{ asset('assets/images/attachment/'. $details['attachment']) }}">Download</a>
        @endif
    </p>
</body>
</html>