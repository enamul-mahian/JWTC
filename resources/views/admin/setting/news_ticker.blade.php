@extends('dashboard.layout.dasboardMaster')
@section('title','News Ticker')
@section('content')
<section class="section dashboard"><div class="card"><div class="card-body p-4"><h1 class="h4">News ticker</h1><p>Publish announcements below the website header. Visitors can pause scrolling; reduced-motion preferences show a static strip.</p>
@if(session('success'))<p class="alert alert-success">{{ session('success') }}</p>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('admin.news-ticker.update') }}">@csrf
<input type="hidden" name="enabled" value="0"><label><input type="checkbox" name="enabled" value="1" @checked(old('enabled',$notice->enabled))> Show news ticker</label>
<div class="my-4"><label for="ticker-speed" class="d-block font-weight-bold">Scrolling speed</label><div class="d-flex align-items-center" style="gap:16px;max-width:550px"><span>Slow</span><input id="ticker-speed" name="speed" type="range" min="15" max="160" step="1" value="{{ old('speed',$notice->speed) }}" class="form-range w-100"><span>Fast</span></div><small>The slider controls how fast headlines move, regardless of their length.</small></div>
<h2 class="h5">Headlines</h2><p>Up to 10 headlines, displayed in this order. Leave unused rows empty. Links are optional.</p>
@for($i=0;$i<10;$i++)<div class="row mb-3"><div class="col-md-7"><label for="ticker-title-{{ $i }}">Headline {{ $i+1 }}</label><input class="form-control" id="ticker-title-{{ $i }}" name="items[{{ $i }}][title]" maxlength="200" value="{{ old('items.'.$i.'.title',$notice->items[$i]['title']??'') }}"></div><div class="col-md-5"><label for="ticker-url-{{ $i }}">Optional link</label><input class="form-control" id="ticker-url-{{ $i }}" name="items[{{ $i }}][url]" maxlength="2048" placeholder="/search-jobs or https://…" value="{{ old('items.'.$i.'.url',$notice->items[$i]['url']??'') }}"></div></div>@endfor
<button class="btn btn-primary">Save news ticker</button><a href="{{ route('home') }}" class="btn btn-outline-primary" target="_blank" rel="noopener">View website</a>
</form></div></div></section>
@endsection
