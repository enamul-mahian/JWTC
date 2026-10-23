@extends('frontend.layout.frontMaster')
@section('title', $title)
@section('content')
<section class="jg-wrap jg-editor"><div class="jg-editor-heading"><span class="jg-pill">Employer workspace</span><h1>Edit job vacancy</h1><p>Clear job details help the right candidates find your opportunity.</p></div>
@if($errors->any())<div class="jg-form-errors" role="alert"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form enctype="multipart/form-data" method="POST" action="{{ route('jobs.update',$job) }}" class="jg-editor-form">@csrf
@method('PUT')
@include('frontend.pages.job.form')
<div class="jg-editor-actions"><button class="jg-button" type="submit">Save changes for review</button><a href="{{ route('employer.dashboard') }}">Back to dashboard</a></div></form></section>
@endsection
