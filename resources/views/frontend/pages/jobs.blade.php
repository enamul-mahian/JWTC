@extends('frontend.layout.frontMaster')
@section('title', 'Latest Job Vacancies | JoinGallagher.uk')
@section('content')
<section class="jg-wrap jg-listing"><h1>Latest Job Vacancies</h1><p>Browse all published jobs. No registration required.</p><div class="jg-listing-grid">
<aside class="jg-filter"><h2>Filters</h2>@include('shared.job_filters')</aside>
<div><div class="jg-listing-count">{{ number_format($jobs->total()) }} published {{ \Illuminate\Support\Str::plural('job', $jobs->total()) }}</div><div class="jg-job-list">@forelse($jobs as $job)@include('frontend.includes.approved_job_card')@empty<div class="jg-empty">No jobs match these filters. Try another keyword or location.</div>@endforelse</div><div class="jg-pagination">{{ $jobs->withQueryString()->links() }}</div></div>
</div></section>
@endsection
