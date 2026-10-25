@extends('frontend.layout.frontMaster')
@section('title', $job->title.' | JoinGallagher.uk')
@push('page-style')
<style>
.jg-site .jg-job-detail{padding-top:28px!important;padding-bottom:52px!important;background:#f4f7fb!important}
.jg-site .jg-job-detail>.jg-detail-card{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;display:grid!important;gap:18px!important}
.jg-site .jg-job-detail>.jg-detail-card>h1,.jg-site .jg-job-detail>.jg-detail-card>.jg-detail-company,.jg-site .jg-job-detail>.jg-detail-card>.jg-job-copy,.jg-site .jg-job-detail>.jg-detail-card>.jg-helper,.jg-site .jg-job-detail>.jg-detail-card>.jg-notice{background:#fff!important;border:1px solid #e0e7f1!important;border-radius:14px!important;padding:22px!important;margin:0!important;box-shadow:0 4px 16px rgba(14,38,80,.04)!important;min-width:0!important}
.jg-site .jg-job-detail>.jg-detail-card>h1{border-bottom:0!important;padding-bottom:12px!important;margin-bottom:-18px!important;border-bottom-left-radius:0!important;border-bottom-right-radius:0!important}
.jg-site .jg-job-detail>.jg-detail-card>.jg-detail-company{border-top-left-radius:0!important;border-top-right-radius:0!important}
.jg-site .jg-job-detail .jg-job-copy{background:#fff!important;background-image:none!important}
.jg-site .jg-job-detail .jg-job-copy h2{color:#102b51!important;font-size:clamp(19px,2vw,23px)!important;margin:0 0 16px!important}
.jg-site .jg-job-detail .jg-job-facts{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:12px!important}
.jg-site .jg-job-detail .jg-job-facts>div{min-width:0!important;overflow-wrap:anywhere;background:#f6f9fe!important;padding:13px!important;border-radius:10px!important}
.jg-site .jg-job-detail .jg-rich-copy{overflow-wrap:anywhere!important;line-height:1.75!important}
.jg-site .jg-job-detail .jg-button{width:fit-content!important;max-width:100%!important}
@media(max-width:700px){.jg-site .jg-job-detail .jg-job-facts{grid-template-columns:repeat(2,minmax(0,1fr))!important}.jg-site .jg-job-detail>.jg-detail-card>h1,.jg-site .jg-job-detail>.jg-detail-card>.jg-detail-company,.jg-site .jg-job-detail>.jg-detail-card>.jg-job-copy{padding:17px!important}}
@media(max-width:440px){.jg-site .jg-job-detail .jg-job-facts{grid-template-columns:1fr!important}}
</style>
@endpush
@section('content')
<section class="jg-wrap jg-job-detail"><a href="{{ route('search-jobs') }}" class="jg-text-link">← Back to jobs</a><div class="jg-detail-card"><h1>{{ $job->title }}</h1><div class="jg-detail-company">@if(\App\Services\JobPresentation::logo($job))<img class="jg-job-logo" src="{{ \App\Services\JobPresentation::logo($job) }}" alt="{{ $job->company?->company_name }} logo">@else<span class="jg-company-monogram" role="img" aria-label="Generated company initials; not an official employer logo">{{ mb_substr($job->company?->company_name ?? 'J', 0, 1) }}</span>@endif<div><h2>{{ $job->company?->company_name ?? 'Hiring employer' }}</h2><div class="jg-job-meta"><span>⌖ {{ $job->country?->name ?? 'See description' }}</span><span>◉ {{ \App\Services\JobPresentation::salarySummary($job) }}</span></div>
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
@if($job->application_deadline)<p class="jg-helper">Application deadline: {{ \Illuminate\Support\Carbon::parse($job->application_deadline)->format('d M Y') }}@if(\App\Services\JobDateRevision::applies($job)) <span title="Closing date updated by the website; confirm current availability with the employer">(website-updated; confirm with employer)</span>@endif</p>@endif
@php($jgFaqs=\App\Services\JobPresentation::faqs($job)) @if($jgFaqs)<section class="jg-job-copy"><h2>Frequently asked questions</h2>@foreach($jgFaqs as $faq)<details class="jg-faq-public"><summary>{{ $job->publicDescription($faq['question']??'') }}</summary><p>{{ $job->publicDescription($faq['answer']??'') }}</p></details>@endforeach</section>@endif</div></section>
@endsection
