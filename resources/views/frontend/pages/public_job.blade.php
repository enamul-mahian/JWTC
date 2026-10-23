@extends('frontend.layout.frontMaster')
@section('title', $job->title.' | JoinGallagher.uk')
@section('content')
<section class="jg-wrap jg-job-detail"><a href="{{ route('search-jobs') }}" class="jg-text-link">← Back to jobs</a><div class="jg-detail-card"><h1>{{ $job->title }}</h1><div class="jg-detail-company"><span class="jg-company-monogram" aria-hidden="true">{{ mb_substr($job->company?->company_name ?? 'J', 0, 1) }}</span><div><h2>{{ $job->company?->company_name ?? 'Hiring employer' }}</h2><div class="jg-job-meta"><span>⌖ {{ $job->country?->name ?? 'See description' }}</span><span>◉ {{ gs()?->currency_symbol }}{{ $job->minimum_salary }} – {{ gs()?->currency_symbol }}{{ $job->maximum_salary }} {{ $job->salary_type ? '('.ucfirst($job->salary_type).')' : '' }}</span></div>
@if($job->publicPosterEmail())<a href="mailto:{{ $job->publicPosterEmail() }}">{{ $job->publicPosterEmail() }}</a>@endif
</div></div>
@if($job->application_deadline && \Illuminate\Support\Carbon::parse($job->application_deadline)->endOfDay()->isPast())<p class="jg-notice">The application deadline for this listing has passed.</p>
@elseif(auth()->check() && auth()->user()->user_type === \App\Enum\UserType::JOB_SEEKER)<a class="jg-button" href="{{ route('job-details', $job->id) }}">Continue to application →</a>
@else<a class="jg-button" href="{{ route('jobseeker.login') }}">Sign in to apply for this job</a><p class="jg-helper">You need a job seeker account and the required application code to apply.</p>@endif
@foreach(['Job Description' => $job->job_context, 'Key Responsibilities' => $job->job_responsibilities, 'Requirements' => $job->additional_requirements, 'Benefits' => $job->other_benefits, 'How to Apply' => $job->apply_procedure] as $heading => $text)
@if($text)<section class="jg-job-copy"><h2>{{ $heading }}</h2><div class="jg-rich-copy">{!! $job->publicDescriptionHtml($text) !!}</div></section>@endif
@endforeach
@if($job->application_deadline)<p class="jg-helper">Application deadline: {{ \Illuminate\Support\Carbon::parse($job->application_deadline)->format('d M Y') }}</p>@endif
</div></section>
@endsection
