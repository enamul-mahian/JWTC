@php($jgModern=request()->routeIs('home','search-jobs','public.job','register','login','jobseeker.login','company.login','company.register','company.approval','admin.login','jobs.create','jobs.edit','jobseeker.application.timeline','registration.otp.*'))
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'JoinGallagher.uk')</title><meta name="description" content="@yield('meta_description', 'Browse job opportunities and manage your recruitment applications.')">
@if(!empty($setting?->favicon))<link rel="icon" href="{{ asset('assets/images/setting/'.$setting->favicon) }}">@endif
@unless($jgModern)@include('frontend.includes.style')@endunless
<link rel="stylesheet" href="{{ asset('theme/approved.css') }}?v=20261023-6">
<link rel="manifest" href="{{ asset('site.webmanifest') }}"><meta name="theme-color" content="#0b2348"><link rel="apple-touch-icon" href="{{ asset('pwa/icon-192.png') }}"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-title" content="JoinGallagher">
<link rel="stylesheet" href="{{ asset('pwa/install.css') }}?v=1">
@stack('style')
<link rel="stylesheet" href="{{ asset('theme/portal-motion.css') }}?v=20261023-1">
</head><body class="jg-site"><a href="#jg-main" class="jg-skip">Skip to main content</a>
@include('frontend.includes.approved_nav')
@include('frontend.includes.news_ticker')
@foreach(['success','info','warning','error'] as $kind)@if(session()->has($kind))<div class="jg-wrap"><div class="jg-flash jg-flash-{{ $kind }}" role="status">{{ session($kind) }}</div></div>@endif
@endforeach
<main id="jg-main">@yield('content')</main>
<footer class="jg-footer"><span>© {{ date('Y') }} JoinGallagher.uk</span><a href="{{ route('contact') }}">Contact</a><a href="{{ route('search-jobs') }}">Browse jobs</a><a href="{{ route('company.login') }}">Company sign in</a><a href="{{ url('/admin/login') }}">Admin login</a><button type="button" class="jg-install-link" data-pwa-manual>Install app</button></footer>
<aside class="jg-install" data-pwa-install aria-label="Install JoinGallagher app" hidden><button type="button" class="jg-install-close" data-pwa-dismiss aria-label="Dismiss install suggestion">×</button><strong>Install JoinGallagher</strong><p data-pwa-message></p><button type="button" class="jg-install-action" data-pwa-action>Install app</button></aside>
@unless($jgModern)@include('frontend.includes.script')@endunless
<script>
const jgNavigation = document.querySelector('.jg-navigation');
const jgWideScreen = window.matchMedia('(min-width: 901px)');
function jgSyncNavigation() { if (jgNavigation) jgNavigation.open = jgWideScreen.matches; }
jgSyncNavigation();
jgWideScreen.addEventListener('change', jgSyncNavigation);
@if(Session::has('success'))window.toastr && toastr.success(@json(Session::get('success')));@endif
@if(Session::has('info'))window.toastr && toastr.info(@json(Session::get('info')));@endif
@if(Session::has('warning'))window.toastr && toastr.warning(@json(Session::get('warning')));@endif
@if(Session::has('error'))window.toastr && toastr.error(@json(Session::get('error')));@endif
@foreach($errors->all() as $error)window.toastr && toastr.error(@json($error));@endforeach
</script>
@stack('js')
@stack('script')
@include('site-chat.widget')
@stack('scripts')
<script defer src="{{ asset('theme/portal-motion.js') }}?v=20261023-1"></script>
<script defer src="{{ asset('pwa/install.js') }}?v=1"></script><script defer src="{{ asset('theme/hero-slides.js') }}?v=1"></script>
</body></html>
