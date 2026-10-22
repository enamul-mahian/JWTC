@extends('frontend.layout.frontMaster')
@section('title', 'Company Registration')
@section('content')
<section class="jg-auth"><div class="jg-auth-card"><span class="jg-pill">For employers</span><h1>Create your company account</h1><p>All company accounts require administrator approval before accessing any workspace tools.</p>
@if($errors->any())<div role="alert" class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('employer-register.store') }}">@csrf
<label for="company-name">Company / organisation name</label><input id="company-name" name="name" required maxlength="255" autocomplete="organization" value="{{ old('name') }}">
<label for="company-email">Business email</label><input id="company-email" name="email" type="email" required autocomplete="email" value="{{ old('email') }}">
<label for="company-phone">Contact phone</label><input id="company-phone" name="phone" type="tel" required autocomplete="tel" value="{{ old('phone') }}">
<label for="company-category">Industry / job category</label><select id="company-category" name="job_categories_id" required><option value="">Select a category</option>@foreach($jobCategories as $category)<option value="{{ $category->id }}" @selected(old('job_categories_id') == $category->id)>{{ $category->name }}</option>@endforeach</select>
<label for="company-password">Password</label><input id="company-password" name="password" type="password" required minlength="8" autocomplete="new-password">
<label for="company-confirm">Confirm password</label><input id="company-confirm" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
@include('shared.hcaptcha')<button class="jg-button">Submit for approval</button>
</form><p>Already registered? <a href="{{ route('company.login') }}">Company sign in</a></p><p>Looking for a job? <a href="{{ route('register') }}">Job seeker registration</a></p></div></section>
@endsection
