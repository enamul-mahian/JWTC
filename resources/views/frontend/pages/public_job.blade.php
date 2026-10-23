@extends('frontend.layout.frontMaster')
@section('title', $job->title.' | JoinGallagher.uk')
@section('content')
<section class="jg-wrap jg-job-detail"><a href="{{ route('search-jobs') }}" class="jg-text-link">← Back to jobs</a><div class="jg-detail-card"><h1>{{ $job->title }}</h1><div class="jg-detail-company">@if(\App\Services\JobPresentation::logo($job))<img class="jg-job-logo" src="{{ \App\Services\JobPresentation::logo($job) }}" alt="{{ $job->company?->company_name }} logo">@else<span class="jg-company-monogram" aria-hidden="true">{{ mb_substr($job->company?->company_name ?? 'J', 0, 1) }}</span>@endif<div><h2>{{ $job->company?->company_name ?? 'Hiring employer' }}</h2><div class="jg-job-meta"><span>⌖ {{ $job->country?->name ?? 'See description' }}</span><span>◉ {{ gs()?->currency_symbol }}{{ $job->minimum_salary }} – {{ gs()?->currency_symbol }}{{ $job->maximum_salary }} {{ $job->salary_type ? '('.ucfirst($job->salary_type).')' : '' }}</span></div>
@if($job->publicPosterEmail())<a href="mailto:{{ $job->publicPosterEmail() }}">{{ $job->publicPosterEmail() }}</a>@endif
</div></div>
@if($job->application_deadline && \Illuminate\Support\Carbon::parse($job->application_deadline)->endOfDay()->isPast())<p class="jg-notice">The application deadline for this listing has passed.</p>
@elseif(auth()->check() && auth()->user()->user_type === \App\Enum\UserType::JOB_SEEKER)<a class="jg-button" href="{{ route('job-details', $job->id) }}">Continue to application →</a>
@else<a class="jg-button" href="{{ route('jobseeker.login') }}">Sign in to apply for this job</a><p class="jg-helper">You need a job seeker account and the required application code to apply.</p>@endif
<section class="jg-job-copy"><h2>Job Summary</h2><dl class="jg-job-facts">
@if($job->vacancies)<div><dt>Vacancies</dt><dd>{{ $job->vacancies }}</dd></div>@endif
@if($job->minimum_year !== null || $job->maximum_year !== null)<div><dt>Experience</dt><dd>{{ $job->minimum_year ?? 0 }}–{{ $job->maximum_year ?? $job->minimum_year }} years</dd></div>@endif
@if($job->fresher)<div><dt>Entry level</dt><dd>Freshers are encouraged to apply.</dd></div>@endif
@if($job->salary_review)<div><dt>Salary review</dt><dd>{{ ucwords(str_replace('_',' ',$job->salary_review)) }}</dd></div>@endif
@if($job->festival_bonus)<div><dt>Festival bonus</dt><dd>{{ $job->festival_bonus }}</dd></div>@endif
@if($job->lunch_facilities)<div><dt>Lunch facilities</dt><dd>{{ ucwords(str_replace('_',' ',$job->lunch_facilities)) }}</dd></div>@endif
</dl></section>
@foreach(['Job Description'  => $job->job_context, 'Key Responsibilities' => $job->job_responsibilities, 'Requirements' => $job->additional_requirements, 'Workplace' => $job->work_place, 'Employment Type' => $job->publicEmploymentLabels(), 'Job Level' => $job->job_level, 'Benefits' => $job->other_benefits, 'Additional Salary Information' => $job->additional_salary_info, 'How to Apply' => $job->apply_procedure] as $heading => $text)
@if($text)<section class="jg-job-copy"><h2>{{ $heading }}</h2><div class="jg-rich-copy">{!! $job->publicDescriptionHtml($text) !!}</div></section>@endif
@endforeach
@if($job->application_deadline)<p class="jg-helper">Application deadline: {{ \Illuminate\Support\Carbon::parse($job->application_deadline)->format('d M Y') }}</p>@endif
@php($jgFaqs=\App\Services\JobPresentation::faqs($job)) @if($jgFaqs)<section class="jg-job-copy"><h2>Frequently asked questions</h2>@foreach($jgFaqs as $faq)<details class="jg-faq-public"><summary>{{ $job->publicDescription($faq['question']??'') }}</summary><p>{{ $job->publicDescription($faq['answer']??'') }}</p></details>@endforeach</section>@endif</div></section>
@endsection
