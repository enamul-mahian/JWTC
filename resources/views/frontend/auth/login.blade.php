@extends('frontend.layout.frontMaster')
@section('title', $title)
@section('content')
<section class="jg-auth"><div class="jg-auth-card">
<span class="jg-pill">{{ $audience === 'employer' ? 'For companies' : ($audience === 'admin' ? 'Administration' : 'For job seekers') }}</span><h1>{{ $title }}</h1>
<p>{{ $audience === 'employer' ? 'Manage your company and vacancies after administrator approval.' : 'Sign in to your account to continue.' }}</p>
@if(session('message'))<div role="alert" class="alert alert-warning">{{ session('message') }}</div>@endif
@if($errors->any())<div role="alert" class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route($audience === 'employer' ? 'company.login.submit' : ($audience === 'admin' ? 'admin.login.submit' : 'jobseeker.login.submit')) }}">@csrf
<label for="portal-email">Email address</label><input id="portal-email" type="email" name="email" required autocomplete="username" value="{{ old('email') }}">
<label for="portal-password">Password</label><input id="portal-password" type="password" name="password" required autocomplete="current-password">
@include('shared.hcaptcha')<button class="jg-button" type="submit">Sign in</button>
</form>
@if($audience !== 'admin')<p>New here? <a href="{{ route($audience === 'employer' ? 'company.register' : 'register') }}">Create {{ $audience === 'employer' ? 'a company' : 'a job seeker' }} account</a></p>
<p><a href="{{ route($audience === 'employer' ? 'jobseeker.login' : 'company.login') }}">{{ $audience === 'employer' ? 'Job seeker sign in' : 'Company sign in' }}</a></p>@endif
</div></section>
@endsection
