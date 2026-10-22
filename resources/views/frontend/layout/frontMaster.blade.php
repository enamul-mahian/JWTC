<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'JoinGallagher.uk')</title><meta name="description" content="@yield('meta_description', 'Browse job opportunities and manage your recruitment applications.')">
@if(!empty($setting?->favicon))<link rel="icon" href="{{ asset('assets/images/setting/'.$setting->favicon) }}">@endif
@include('frontend.includes.style')
<link rel="stylesheet" href="{{ asset('theme/approved.css') }}?v=20261023">
@stack('style')
</head><body class="jg-site"><a href="#jg-main" class="jg-skip">Skip to main content</a>
@include('frontend.includes.approved_nav')
<main id="jg-main">@yield('content')</main>
<footer class="jg-footer"><span>© {{ date('Y') }} JoinGallagher.uk</span><a href="{{ route('contact') }}">Contact</a><a href="{{ route('search-jobs') }}">Browse jobs</a><a href="{{ route('company.login') }}">Company sign in</a></footer>
@include('frontend.includes.script')
@stack('scripts')
</body></html>
