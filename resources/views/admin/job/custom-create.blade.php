@extends('frontend.layout.frontMaster')
@section('title', 'Create Job')
@section('content')
<section class="jg-wrap jg-editor">
<h1>Create Job (Admin)</h1>
<p>Choose a registered company. New jobs are saved as unpublished drafts.</p>
@if($errors->any())<ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
<form method="POST" action="{{ route('admin.custom-jobs.store') }}" enctype="multipart/form-data" class="jg-editor-form">
@csrf
<label>Company <select name="company_id" required><option value="">Choose company</option>
@foreach($companies as $item)<option value="{{ $item->id }}" @selected(old('company_id') == $item->id)>{{ $item->company_name }}</option>@endforeach
</select></label>
@include('frontend.pages.job.form')
<button type="submit">Save as Draft</button>
</form>
</section>
@endsection
