<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title', 'Secure Workspace') · Join Gallagher</title>
    @stack('styles')
    <style>
      :root { color-scheme:light; --ink:#13243c; --muted:#58667b; --line:#dbe3ed; --brand:#1553a3; --paper:#fff; --wash:#f5f7fb; }
      * {box-sizing:border-box} body {margin:0;background:var(--wash);color:var(--ink);font:16px/1.5 system-ui,-apple-system,Segoe UI,sans-serif}
      a{color:var(--brand)} a:focus-visible,button:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible{outline:3px solid #e5a42d;outline-offset:2px}
      header.top{background:#112a4b;color:#fff} header.top .wrap{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1rem 0}
      header.top a{color:#fff;text-decoration:none}header.top .brand{font-size:1.12rem;font-weight:750;letter-spacing:.02em}
      main{max-width:1100px;margin:2rem auto;padding:0 1rem 3rem} .wrap{max-width:1100px;margin:auto;padding:0 1rem}
      h1{font-size:clamp(1.5rem,3vw,2.15rem);line-height:1.2}h2{font-size:1.2rem;margin-top:0}
      .muted{color:var(--muted)}.small{font-size:.875rem}.card{background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:1.35rem;margin:1.1rem 0}
      .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1rem}.grid>*{min-width:0}.row{display:flex;gap:.75rem;align-items:center;flex-wrap:wrap}
      label{display:block;font-weight:650;margin:.7rem 0 .3rem}input,select,textarea{width:100%;padding:.7rem;border:1px solid #a7b6c9;border-radius:7px;background:#fff;font:inherit;color:inherit}
      textarea{min-height:90px}.button,button{border:1px solid var(--brand);background:var(--brand);color:#fff;padding:.67rem 1rem;border-radius:8px;display:inline-block;font:inherit;font-weight:650;cursor:pointer;text-decoration:none}
      .secondary{background:#fff;color:var(--brand)}button:disabled{opacity:.6;cursor:not-allowed}.alert{padding:1rem;border:1px solid #d2dfed;background:#eaf4fd;border-radius:9px;margin:1rem 0}
      .error{border-color:#f0b7b7;background:#fff0f0;color:#8a1c1c}.badge{display:inline-block;border-radius:100px;background:#e8f0fc;color:#143f77;padding:.25rem .7rem;font-size:.8rem;font-weight:700}
      .table-wrap{overflow-x:auto}table{border-collapse:collapse;width:100%;min-width:550px}th,td{text-align:left;padding:.85rem;border-bottom:1px solid var(--line);vertical-align:top}th{color:var(--muted);font-size:.84rem}
      ol.timeline{list-style:none;margin:0;padding:0;border-left:2px solid #d0ddee}ol.timeline>li{position:relative;padding:0 0 1.3rem 1.2rem}ol.timeline>li:before{content:"";position:absolute;left:-6px;top:6px;border:5px solid var(--brand);border-radius:50%}
      .back{display:inline-flex;margin:.2rem 0 .7rem}.empty{text-align:center;padding:2rem;color:var(--muted)}
      footer{border-top:1px solid var(--line);padding:1rem 0;text-align:center;color:var(--muted);font-size:.82rem}
      @media(max-width:600px){main{margin:1rem auto} .card{padding:1rem}header.top .wrap{flex-wrap:wrap}}
    </style>
<link rel="stylesheet" href="{{ asset('theme/approved.css') }}?v=20261023-7"><link rel="stylesheet" href="{{ asset('theme/workspace.css') }}?v=20261023-1">
</head>
<body class="jg-site">
    @include('frontend.includes.approved_nav')
    <main id="main" class="jg-private-shell">
    @if(auth()->user()?->user_type===\App\Enum\UserType::JOB_SEEKER)<div class="jg-profile-grid"><aside class="jg-profile-nav"><h2>My account</h2>@include('jobseeker.partials.sidenav')</aside><div class="jg-profile-main">@endif

        @if(session('success'))<div class="alert" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())
            <div class="alert error" role="alert">
                <strong>Unable to complete your request</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    @if(auth()->user()?->user_type===\App\Enum\UserType::JOB_SEEKER)</div></div>@endif
    </main>
    <footer>Private records are accessible only to authorized account holders and reviewers. Never share passwords or verification codes.</footer>
<script>const n=document.querySelector('.jg-navigation');if(n)n.open=matchMedia('(min-width:901px)').matches;</script>
</body>
</html>
