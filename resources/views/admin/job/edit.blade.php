@extends('dashboard.layout.dasboardMaster')
@section('title','Edit Job')
@push('style')<link rel="stylesheet" href="{{ asset('theme/approved.css') }}?v=20261023-6">@endpush
@section('content')<div class="jg-site jg-editor"><h1>Edit job vacancy</h1><p>Manage job details for any employer. The original job URL and ownership stay intact.</p>
@if(session('success'))<p class="alert alert-success">{{ session('success') }}</p>@endif
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
<form enctype="multipart/form-data" method="POST" action="{{ route('admin.jobs.update',$job) }}">@csrf @method('PATCH')
@include('frontend.pages.job.form')
<div class="jg-editor-panel jg-form-grid"><label>Publication<select name="is_publish"><option value="1" @selected(old('is_publish',$job->is_publish)==1)>Published</option><option value="0" @selected(old('is_publish',$job->is_publish)==0)>Not published</option></select></label><label>Post date<input type="date" name="created_at" value="{{ old('created_at',$job->created_at?->format('Y-m-d')) }}"></label></div>
<div class="jg-editor-actions"><button class="jg-button">Save job</button><a href="{{ route('admin.jobs.index') }}">All jobs</a></div></form></div>@endsection
