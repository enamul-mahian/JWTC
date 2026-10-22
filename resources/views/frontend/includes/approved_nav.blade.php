<header class="jg-header"><nav class="jg-nav" aria-label="Main navigation">
<a class="jg-brand" href="{{ route('home') }}">JoinGallagher<span>.uk</span></a>
<details class="jg-navigation"><summary aria-label="Open navigation">☰</summary><div class="jg-nav-links">
<a href="{{ route('search-jobs') }}" @if(request()->routeIs('home','search-jobs','public.job')) aria-current="page" @endif>Jobs</a>
<a href="{{ route('company.register') }}">Employers</a><a href="{{ route('contact') }}">Contact</a>
@foreach(($pages ?? []) as $page)<a href="{{ route('get-page-details', $page->url) }}">{{ $page->name }}</a>@endforeach
@guest<a class="jg-nav-login" href="{{ route('jobseeker.login') }}">Job seeker login</a><a class="jg-button jg-button-small" href="{{ route('register') }}">Register</a><a class="jg-company-link" href="{{ route('company.login') }}">Company login</a>
@else
@if(auth()->user()->user_type === \App\Enum\UserType::JOB_SEEKER)<a href="{{ route('jobseeker.dashboard') }}">My dashboard</a>
@elseif(auth()->user()->user_type === \App\Enum\UserType::EMPLOYER)<a href="{{ route('employer.dashboard') }}">Company dashboard</a>
@else<a href="{{ route('admin.dashboard') }}">Admin dashboard</a>@endif
<form method="POST" action="{{ route('logout') }}">@csrf<button class="jg-link-button">Sign out</button></form>
@endguest
</div></details></nav></header>
