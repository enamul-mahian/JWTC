@extends('frontend.layout.frontMaster')
@section('title','Government-sourced Vacancies | JoinGallagher.uk')
@section('content')
<section class="jg-wrap jg-listing" aria-labelledby="gov-jobs-heading">
<h1 id="gov-jobs-heading">Government-sourced vacancies</h1>
<p>Jobs originally listed on <a href="https://www.jobs.service.gov.uk/jobs/search" target="_blank" rel="noopener noreferrer">UK Work Hub</a>. These are external listings, not jobs posted by JoinGallagher or its employers. Applications take place on the original website.</p>
<form method="GET" action="{{ route('government-jobs.index') }}" class="d-flex flex-wrap gap-2 mb-4">
<label for="government-job-search" class="visually-hidden">Search external jobs</label>
<input id="government-job-search" name="q" value="{{ request('q') }}" class="form-control" style="max-width:380px" placeholder="Job title, employer or location" maxlength="120">
<button class="btn btn-primary" type="submit">Search</button>
</form>
<p>{{ $jobs->total() }} currently available external vacancies.</p>
<div class="jg-job-list">
@forelse($jobs as $job)
<article class="card mb-3"><div class="card-body">
<p class="text-muted mb-2">External vacancy · UK Work Hub</p>
<h2 class="h5"><a href="{{ route('government-jobs.show',$job->id) }}">{{ $job->title }}</a></h2>
<p class="mb-2"><strong>{{ $job->company_name }}</strong>@if($job->location) · {{ $job->location }}@endif</p>
<p class="mb-2">Full-time · Permanent · On-site</p>
@if($job->salary)<p>{{ $job->salary }}</p>@endif
<p class="small">Posted {{ $job->posted_on }} · Closes {{ $job->application_deadline }}</p>
<a class="btn btn-outline-primary btn-sm" href="{{ route('government-jobs.show',$job->id) }}">View vacancy and apply link</a>
</div></article>
@empty
<div class="jg-empty">There are no currently available government-sourced vacancies matching these criteria.</div>
@endforelse
</div>
<div class="jg-pagination">{{ $jobs->links() }}</div>
<p class="mt-4"><a href="{{ route('search-jobs') }}">Browse jobs posted directly on JoinGallagher</a></p>
</section>
@endsection
