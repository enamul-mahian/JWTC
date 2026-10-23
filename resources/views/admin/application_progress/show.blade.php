@extends('dashboard.layout.dasboardMaster')
@section('title','Application Progress')
@push('style')<link rel="stylesheet" href="{{ asset('theme/approved.css') }}?v=20261023-6">@endpush
@section('content')
<div class="card"><div class="card-body"><h1 class="h4">Manage application #APP-{{ $application->id }}</h1>
@if(session('success'))<p class="alert alert-success">{{ session('success') }}</p>@endif
@if($errors->any())<p class="alert alert-danger">{{ $errors->first() }}</p>@endif
<form method="POST" action="{{ route('admin.application-progress.update',$application->id) }}">@csrf @method('PATCH')<div class="row"><div class="col-md-5"><label for="application-stage">Application stage</label><select class="form-control" id="application-stage" name="status" required>@foreach($statuses as $stage)@if($stage['is_active'] || $stage['id']==$application->status)<option value="{{ $stage['id'] }}" @selected(old('status',$application->status)==$stage['id'])>{{ $stage['name'] }} — {{ $stage['progress'] }}%</option>@endif
@endforeach</select></div><div class="col-md-7"><label for="application-note">Update note (visible to applicant)</label><textarea name="note" id="application-note" class="form-control" rows="2" maxlength="1000">{{ old('note') }}</textarea></div></div><button class="btn btn-primary mt-3">Save progress update</button></form></div></div>
<div class="jg-site">@include('shared.application_tracker')</div>
@endsection
