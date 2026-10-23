<div class="jg-application-list">
@forelse ($jobs as $item)
@php
$application = $item->applications->first();
$applicationStatus = $application ? getJobApplicationStatus((int) $application->status) : null;
$progress = max(0, min(100, (int) ($applicationStatus['progress'] ?? 0)));
@endphp
<article class="jg-profile-card jg-application-card">
<div class="jg-application-summary">
@if(\App\Services\JobPresentation::logo($item))
<img class="jg-job-logo" src="{{ \App\Services\JobPresentation::logo($item) }}" alt="{{ $item->company?->company_name }} logo">
@else
<span class="jg-company-monogram" aria-hidden="true">{{ mb_substr($item->company?->company_name ?? 'J', 0, 1) }}</span>
@endif
<div><h2><a href="{{ route('public.job', $item->id) }}">{{ $item->title }}</a></h2>
<p>{{ $item->company?->company_name ?? 'Hiring employer' }}</p>
<div class="jg-job-meta"><span>{{ $item->country?->name ?? 'See job details' }}</span>
@if($item->minimum_salary || $item->maximum_salary)<span>{{ gs()?->currency_symbol }}{{ $item->minimum_salary }} – {{ gs()?->currency_symbol }}{{ $item->maximum_salary }} {{ $item->salary_type }}</span>@endif</div>
</div></div>
@if($applicationStatus)
<div class="jg-application-status"><p><strong>Status:</strong> {{ $applicationStatus['name'] }}</p>
<div class="jg-application-meter"><progress max="100" value="{{ $progress }}" aria-label="Application progress">{{ $progress }}%</progress><strong>{{ $progress }}%</strong></div></div>
@else<p>Status not available</p>@endif
@if($application)<a class="jg-button" href="{{ route('jobseeker.application.timeline', $application->id) }}">View progress &amp; status history</a>@endif
</article>
@empty<div class="jg-empty">You have not applied for any jobs yet. <a href="{{ route('find-jobs') }}">Find jobs</a></div>
@endforelse
</div>
<div class="jg-pagination">{{ $jobs->withQueryString()->links('shared.pagination') }}</div>
