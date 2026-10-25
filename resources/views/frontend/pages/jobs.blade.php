@extends('frontend.layout.frontMaster')
@section('title', 'Latest Job Vacancies | JoinGallagher.uk')
@section('content')
<section class="jg-wrap jg-listing"><h1>Latest Job Vacancies</h1><p>Browse all published jobs. No registration required.</p><div class="jg-listing-grid">
<aside class="jg-filter"><h2>Filters</h2>@include('shared.job_filters')</aside>
<div><div class="jg-listing-count">{{ number_format($jobs->total()) }} published {{ \Illuminate\Support\Str::plural('job', $jobs->total()) }}</div><div class="jg-job-list">@forelse($jobs as $job)@include('frontend.includes.approved_job_card')@empty<div class="jg-empty">No jobs match these filters. Try another keyword or location.</div>@endforelse</div><div class="jg-pagination">{{ $jobs->withQueryString()->links() }}</div></div>

@if($governmentJobs->isNotEmpty())
<section class="mt-4" aria-labelledby="government-sourced-jobs">
<h2 class="h4" id="government-sourced-jobs">Government-sourced external vacancies</h2>
<p class="small text-muted">Originally posted on UK Work Hub. These employers are external; applications open on the original source website.</p>
@foreach($governmentJobs as $external)
<article class="card mb-3"><div class="card-body">
<h3 class="h5 mb-1"><a href="{{ route('government-jobs.show',$external->id) }}">{{ $external->title }}</a></h3>
<p class="mb-1">{{ $external->company_name }}@if($external->location) · {{ $external->location }}@endif</p>
<p class="small mb-1">Permanent · Full-time · On-site · Closes {{ $external->application_deadline }}</p>
<a href="{{ route('government-jobs.show',$external->id) }}" class="link-primary">Original job information &amp; apply link</a>
</div></article>
@endforeach
<a href="{{ route('government-jobs.index') }}" class="btn btn-outline-primary mt-2">See all government-sourced vacancies</a>
</section>
@else
<div class="mt-4"><a href="{{ route('government-jobs.index') }}">Browse government-sourced external vacancies</a></div>
@endif
</div></section>
@endsection
