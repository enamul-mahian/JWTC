@extends('jobseeker.layout.workspace')
@section('title', 'My Profile')
@section('workspace_title', 'My Profile')
@section('workspace_description', 'Your professional profile, all in one place.')
@section('workspace_content')
@php
$info = $user->personal_info;
$phones = collect([$info?->phone, $info?->alternate_phone, $user->phone])->filter()->map(fn($v)=>trim($v))->unique();
$emails = collect([$info?->email ?: $user->email, $info?->alternate_email])->filter()->map(fn($v)=>trim($v))->unique(fn($v)=>strtolower($v));
$location = collect([$info?->present_city, $info?->present_country])->filter()->implode(', ');
$checks = [
    'name' => filled($user->name), 'email' => $emails->isNotEmpty(),
    'phone' => $phones->isNotEmpty(), 'location' => filled($location),
    'introduction' => filled($info?->about_me), 'education' => $user->educations->isNotEmpty(),
    'experience' => $user->work_experiences->isNotEmpty(), 'skills' => $user->skills->isNotEmpty(),
    'languages' => $user->languages->isNotEmpty(), 'photo' => filled($user->photo),
];
$readiness = (int) round(collect($checks)->filter()->count() / count($checks) * 100);
$missing = collect($checks)->filter(fn($complete)=>!$complete)->keys()->first();
$photo = $user->photo ? (str_starts_with($user->photo, 'profile-photos/') ? asset('storage/'.$user->photo) : asset('assets/images/profilephoto/'.$user->photo)) : null;
$edit = route('job-seeker-profile');
$formatDate = function($date) { try { return $date ? \Illuminate\Support\Carbon::parse($date)->format('M Y') : null; } catch(\Throwable $e) { return $date; } };
@endphp
<section class="jg-cv-summary" aria-labelledby="profile-name">
<div class="jg-cv-identity">
@if($photo)<img class="jg-cv-avatar" src="{{ $photo }}" alt="{{ $user->name }}">@else<span class="jg-cv-avatar jg-cv-initial" aria-hidden="true">{{ mb_substr($user->name,0,1) }}</span>@endif
<div><h2 id="profile-name">{{ $user->name }}</h2>
@if($info?->motto)<p class="jg-cv-profession">{{ $info->motto }}</p>@endif
@if($location)<p class="jg-cv-location">@include('jobseeker.partials.icon',['icon'=>'location']) {{ $location }}</p>@endif
@if($user->email_verified_at)<span class="jg-cv-verified">✓ Email verified</span>@endif
</div></div>
<div class="jg-cv-actions"><a class="jg-cv-outline" href="{{ route('jobseeker-generate-pdf') }}">@include('jobseeker.partials.icon',['icon'=>'download']) Download CV</a><a class="jg-button" href="{{ $edit }}">@include('jobseeker.partials.icon',['icon'=>'edit']) Edit Profile</a></div>
<div class="jg-cv-contact">@foreach($emails as $email)<span>@include('jobseeker.partials.icon',['icon'=>'mail']) {{ $email }}</span>@endforeach @foreach($phones as $phone)<span>@include('jobseeker.partials.icon',['icon'=>'phone']) {{ $phone }}</span>@endforeach</div>
</section>
<div class="jg-cv-columns"><div class="jg-cv-primary">
@if(filled($info?->about_me))
<section class="jg-cv-card"><div class="jg-cv-heading"><h2>@include('jobseeker.partials.icon',['icon'=>'file']) About Me</h2><a href="{{ $edit }}">Edit</a></div><p class="jg-cv-copy">{{ $info->about_me }}</p></section>
@endif
<section class="jg-cv-card"><div class="jg-cv-heading"><h2>@include('jobseeker.partials.icon',['icon'=>'briefcase']) Work Experience</h2><a href="{{ $edit }}#profile-table">Edit</a></div>
@forelse($user->work_experiences as $work)
<article class="jg-cv-entry"><span class="jg-cv-entry-icon">@include('jobseeker.partials.icon',['icon'=>'briefcase'])</span><div class="jg-cv-entry-body"><div class="jg-cv-entry-title"><h3>{{ $work->position_name ?: 'Work experience' }}</h3><span>{{ $formatDate($work->from_date) }}{{ $work->from_date ? ' – ' : '' }}{{ $work->continuing ? 'Present' : $formatDate($work->to_date) }}</span></div><p>{{ collect([$work->employeer, $work->city, $work->occupation_country])->filter()->implode(', ') }}</p>@if($work->responsibilities)<p class="jg-cv-copy">{{ $work->responsibilities }}</p>@endif</div></article>
@empty<p class="jg-cv-empty">No work experience added yet. <a href="{{ $edit }}#profile-table">Add experience</a></p>@endforelse
</section>
<section class="jg-cv-card"><div class="jg-cv-heading"><h2>@include('jobseeker.partials.icon',['icon'=>'education']) Education</h2><a href="{{ $edit }}#profile-table">Edit</a></div>
@forelse($user->educations as $education)
<article class="jg-cv-entry"><span class="jg-cv-entry-icon">@include('jobseeker.partials.icon',['icon'=>'education'])</span><div class="jg-cv-entry-body"><div class="jg-cv-entry-title"><h3>{{ $education->degreeName?->name ?: $education->degreeLevel?->name ?: 'Education' }}</h3><span>{{ $education->pass_year }}</span></div><p>{{ $education->institute_name }}</p>@if(filled($education->result))<p class="jg-cv-muted">Result: {{ $education->result }}</p>@endif</div></article>
@empty<p class="jg-cv-empty">No education added yet. <a href="{{ $edit }}#profile-table">Add education</a></p>@endforelse
</section>
@if($user->trainings->isNotEmpty())
<section class="jg-cv-card"><div class="jg-cv-heading"><h2>@include('jobseeker.partials.icon',['icon'=>'education']) Training &amp; Certificates</h2><a href="{{ $edit }}#profile-table">Edit</a></div>@foreach($user->trainings as $training)<article class="jg-cv-entry"><div class="jg-cv-entry-body"><div class="jg-cv-entry-title"><h3>{{ $training->title }}</h3><span>{{ $training->training_year }}</span></div><p>{{ $training->training_institute }}</p>@if($training->topic)<p class="jg-cv-copy">{{ $training->topic }}</p>@endif</div></article>@endforeach</section>
@endif
</div><aside class="jg-cv-secondary">
<section class="jg-cv-card"><div class="jg-cv-heading"><h2>@include('jobseeker.partials.icon',['icon'=>'chart']) Profile Readiness</h2><strong class="jg-cv-percentage">{{ $readiness }}%</strong></div><progress class="jg-cv-progress" value="{{ $readiness }}" max="100" aria-label="Profile readiness">{{ $readiness }}%</progress><p class="jg-cv-muted">{{ $missing ? 'Add your '.$missing.' to improve your profile.' : 'Your profile information is complete.' }}</p></section>
<section class="jg-cv-card"><div class="jg-cv-heading"><h2>@include('jobseeker.partials.icon',['icon'=>'skills']) Skills</h2><a href="{{ $edit }}#profile-table">Edit</a></div><div class="jg-cv-chips">@forelse($user->skills as $skill)<span class="jg-cv-chip">{{ $skill->skill_name }}@if($skill->skill_proficiency)<small>{{ $skill->skill_proficiency }}</small>@endif</span>@empty<p class="jg-cv-empty">No skills added yet.</p>@endforelse</div></section>
<section class="jg-cv-card"><div class="jg-cv-heading"><h2>@include('jobseeker.partials.icon',['icon'=>'globe']) Languages</h2><a href="{{ $edit }}#profile-table">Edit</a></div>@forelse($user->languages as $language)<div class="jg-cv-language"><strong>{{ $language->language_name }}</strong><dl>@foreach(['Listening'=>'listening_proficiency','Reading'=>'reading_proficiency','Writing'=>'writting_proficiency'] as $label=>$field)@if($language->{$field})<div><dt>{{ $label }}</dt><dd>{{ $language->{$field} }}</dd></div>@endif @endforeach</dl></div>@empty<p class="jg-cv-empty">No languages added yet.</p>@endforelse</section>
<section class="jg-cv-card"><div class="jg-cv-heading"><h2>@include('jobseeker.partials.icon',['icon'=>'lock']) Private Documents</h2></div><p class="jg-cv-muted">Only you and authorized reviewers can access your documents.</p>@if($passportSummary ?? null)<div class="jg-cv-passport">@include('jobseeker.partials.icon',['icon'=>'file'])<div><strong>Passport</strong><span>{{ $passportSummary['number'] }}</span></div><span class="jg-cv-verified">Saved</span></div>@else<p class="jg-cv-empty">No passport information added yet.</p>@endif<a class="jg-cv-outline jg-cv-full" href="{{ route('jobseeker.private-documents.index') }}">@include('jobseeker.partials.icon',['icon'=>'file']) Manage Documents</a></section>
</aside></div>
@endsection
