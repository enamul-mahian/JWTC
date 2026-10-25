@extends('frontend.layout.frontMaster')
@section('title',$job->title.' | Government-sourced vacancy')
@section('content')
<section class="jg-wrap jg-listing" aria-labelledby="government-job-title">
<p><a href="{{ route('government-jobs.index') }}">&larr; All government-sourced vacancies</a></p>
<p class="text-muted">External vacancy · Originally published on UK Work Hub</p>
<h1 id="government-job-title">{{ $job->title }}</h1>
<h2 class="h5">{{ $job->company_name }}</h2>
@if($job->location)<p><strong>Location:</strong> {{ $job->location }}</p>@endif
<p><strong>Working pattern:</strong> On-site · Full-time · Permanent</p>
@if($job->salary)<p><strong>Salary:</strong> {{ $job->salary }}</p>@endif
<p><strong>Posted:</strong> {{ $job->posted_on }} · <strong>Closing date:</strong> {{ $job->application_deadline }}</p>
@if($job->summary)<div class="card my-4"><div class="card-body"><h2 class="h5">About this vacancy</h2><p>{{ $job->summary }}</p></div></div>@endif
<div class="alert alert-info" role="note">This is an externally sourced listing. JoinGallagher does not accept applications for it. Check full requirements, employer identity and availability on the original Work Hub website before applying.</div>
<a class="btn btn-primary" href="{{ $job->source_url }}" target="_blank" rel="noopener noreferrer">View original vacancy and apply</a>
</section>
@endsection
