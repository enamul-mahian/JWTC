@extends('frontend.layout.frontMaster')
@section('title', 'Company approval pending')
@section('content')
<section class="jg-auth"><div class="jg-auth-card"><span class="jg-pill">Company account</span><h1>Awaiting administrator approval</h1><p>Your account is registered. An administrator must approve it before you can use the company workspace, post jobs or access applicants.</p><p>Approval is checked on every request. You will regain access here once your account is approved.</p><form method="POST" action="{{ route('logout') }}">@csrf<button class="jg-button">Sign out</button></form></div></section>
@endsection
