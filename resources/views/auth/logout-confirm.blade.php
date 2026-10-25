<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Sign out | JoinGallagher.uk</title>
    <style>
        body{background:#f3f7fc;color:#10264e;font:16px system-ui,-apple-system,sans-serif;margin:0;padding:32px 16px}
        main{max-width:460px;margin:12vh auto;padding:30px;background:#fff;border:1px solid #dce5f2;border-radius:16px}
        h1{font-size:27px;line-height:1.3;margin:0 0 12px}
        p{color:#50637f;line-height:1.6}
        button{background:#0758df;color:#fff;border:0;border-radius:9px;padding:13px 20px;font:600 15px system-ui;cursor:pointer}
        a{color:#0758df;margin-left:15px}
        :focus-visible{outline:3px solid #e9ae36;outline-offset:3px}
    </style>
</head>
<body>
<main>
    <h1>Sign out securely</h1>
    <p>Your earlier sign-out request may have expired. Continue to safely end this session.</p>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit">Confirm sign out</button>
        <a href="{{ route('home') }}">Back to website</a>
    </form>
</main>
</body>
</html>
