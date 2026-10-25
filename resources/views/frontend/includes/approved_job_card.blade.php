<article class="jg-job-card">@if(\App\Services\JobPresentation::logo($job))<img class="jg-job-logo" src="{{ \App\Services\JobPresentation::logo($job) }}" alt="{{ $job->company?->company_name }} logo">@else<div class="jg-company-monogram" role="img" aria-label="Generated company initials; not an official employer logo">{{ mb_substr($job->company?->company_name ?? 'J', 0, 1) }}</div>@endif<div class="jg-job-body">
@php($closingDate=$job->application_deadline ? substr((string)$job->application_deadline,0,10) : null)
@php($dateRevision=\App\Services\JobDateRevision::note($job))
@if($closingDate && $closingDate < now()->toDateString())
<p class="jg-vacancy-status jg-vacancy-closed"><strong>Applications closed</strong> · Deadline {{ \Illuminate\Support\Carbon::parse($closingDate)->format('d M Y') }}</p>
@elseif($closingDate)
<p class="jg-vacancy-status jg-vacancy-open"><strong>{{ $dateRevision ? 'Closing date (verify with employer)' : 'Applications open' }}</strong> · Until {{ \Illuminate\Support\Carbon::parse($closingDate)->format('d M Y') }}</p>
@else
<p class="jg-vacancy-status jg-vacancy-unknown"><strong>Deadline unconfirmed</strong> · Check with employer</p>
@endif
<h3><a href="{{ route('public.job', $job->id) }}">{{ $job->title }}</a></h3><p>{{ $job->company?->company_name ?? 'Hiring employer' }}</p><div class="jg-job-meta"><span>⌖ {{ $job->country?->name ?? 'See job details' }}</span><span>▣ {{ $job->job_category?->name ?? 'Job opportunity' }}</span>@if($job->minimum_salary || $job->maximum_salary)<span>◉ {{ \App\Services\JobPresentation::salarySummary($job) }}</span>@endif</div><p class="jg-job-summary">{{ \Illuminate\Support\Str::limit($job->publicDescription($job->job_context ?? $job->job_responsibilities ?? ''), 140) }}</p><div class="jg-job-bottom"><a href="{{ route('public.job', $job->id) }}">View details →</a>@if($job->created_at)<time datetime="{{ $job->created_at->toIso8601String() }}" title="Original date posted">Originally posted {{ $job->created_at->format('d M Y') }}</time>@endif</div></div></article>
