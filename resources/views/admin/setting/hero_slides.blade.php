@extends('dashboard.layout.dasboardMaster')
@section('title','Homepage Slides')
@section('content')
<section class="section dashboard"><div class="card"><div class="card-body p-4"><h1 class="h4">Homepage slides</h1><p>Change the homepage background images and their order. Switching the slideshow off keeps the first image visible.</p>
@if(session('success'))<p class="alert alert-success">{{ session('success') }}</p>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.hero-slides.update') }}">@csrf
<input type="hidden" name="enabled" value="0"><label><input type="checkbox" name="enabled" value="1" @checked(old('enabled',$hero->enabled))> Automatically change slides</label>
<div class="my-3"><label for="hero-interval">Seconds between slides</label><input id="hero-interval" name="interval" type="number" min="5" max="30" value="{{ old('interval',$hero->interval) }}" class="form-control" style="max-width:150px"></div>
<div class="row">@foreach($hero->slides as $i=>$slide)<div class="col-md-6 mb-4"><img src="{{ asset($slide['path']) }}" alt="Background slide {{ $i+1 }}" style="width:100%;height:150px;object-fit:cover;border-radius:8px"><input type="hidden" name="slides[{{ $i }}][id]" value="{{ $slide['id'] }}"><label for="hero-order-{{ $i }}">Display order</label><input id="hero-order-{{ $i }}" type="number" name="slides[{{ $i }}][order]" min="1" max="99" value="{{ old('slides.'.$i.'.order',$i+1) }}" class="form-control"><label><input type="checkbox" name="slides[{{ $i }}][remove]" value="1" @checked(old('slides.'.$i.'.remove',false))> Remove from slideshow</label></div>@endforeach</div>
<div class="my-3"><label for="hero-images">Add background images</label><input class="form-control" id="hero-images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple><small>Up to five slides total. JPG, PNG or WebP, up to 5 MB each, 5000 × 3000 pixels and 12 megapixels. Wide landscape images work best. Images are optimized automatically. New images are added after existing slides; save, then change their order. Removed images are retained for recovery.</small></div>
<button class="btn btn-primary">Save homepage slides</button><a class="btn btn-outline-primary" href="{{ route('home') }}" target="_blank" rel="noopener">View website</a>
</form></div></div></section>
@endsection
