@extends('dashboard.layout.dasboardMaster')
@section('title', 'Create Custom Job')
@push('style')<link rel="stylesheet" href="{{ asset('theme/approved.css') }}?v=20261023-7">@endpush
@section('content')
<section class="jg-site jg-editor" aria-labelledby="admin-custom-job-title">
<h1 id="admin-custom-job-title">Create custom job</h1>
<p>Create a job on behalf of an approved employer. The employer is credited as the poster, and the job will be visible publicly only if you choose <strong>Publish now</strong>.</p>
@if($errors->any())<div class="alert alert-danger" role="alert"><strong>Please correct these fields:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if($companies->isEmpty())<div class="alert alert-warning">No approved employer profiles are available. Approve an employer before creating a job on their behalf.</div>@endif
<form enctype="multipart/form-data" method="POST" action="{{ route('admin.jobs.store') }}">
@csrf
@include('frontend.pages.job.form')
<div class="jg-editor-panel jg-form-grid">
<label>Publication
<select name="is_publish" required><option value="0" @selected(old('is_publish','0')==='0')>Save as draft</option><option value="1" @selected(old('is_publish')==='1')>Publish now</option></select>
</label>
<label class="jg-field-wide"><input type="checkbox" name="posting_permission" value="1" @checked(old('posting_permission')==1) required> I confirm that this employer has authorized this vacancy and its application instructions.</label>
</div>
<div class="jg-editor-actions"><button class="jg-button" type="submit" @disabled($companies->isEmpty())>Create Job</button><a href="{{ route('admin.jobs.index') }}">Back to All Jobs</a></div>
</form>
</section>
@endsection
