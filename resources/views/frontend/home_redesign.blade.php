{{-- Opt-in accessible homepage; no legacy hero image or live applicant data replaced. --}}
<div class="jw-home">
    <section class="jw-hero" aria-labelledby="jw-hero-title">
        <div>
            <span class="jw-eyebrow">Join Gallagher · Recruitment Platform</span>
            <h1 id="jw-hero-title">Find your next opportunity with confidence.</h1>
            <p>Discover published job opportunities, manage applications, and stay connected with your recruitment team in one place.</p>
            <div class="jw-actions">
                <a class="jw-button" href="{{ route('search-jobs') }}">Explore opportunities <span aria-hidden="true">↗</span></a>
                @guest
                    <a class="jw-button alt" href="{{ route('registration.form') }}">Create an account</a>
                @else
                    @if(auth()->user()->user_type === \App\Enum\UserType::JOB_SEEKER)
                        <a class="jw-button alt" href="{{ route('jobseeker.dashboard') }}">My dashboard</a>
                    @elseif(auth()->user()->user_type === \App\Enum\UserType::EMPLOYER)
                        <a class="jw-button alt" href="{{ route('employer.dashboard') }}">Employer dashboard</a>
                    @elseif(in_array(auth()->user()->user_type, [\App\Enum\UserType::ADMIN,\App\Enum\UserType::SUPER_ADMIN], true))
                        <a class="jw-button alt" href="{{ route('admin.dashboard') }}">Admin dashboard</a>
                    @endif
                @endguest
            </div>
        </div>
        <div class="jw-hero-panel">
            <h2>Search job opportunities</h2>
            <form action="{{ route('search-jobs') }}" method="GET" role="search">
                <label for="jw-job-search">Job title or keyword</label>
                <input type="search" id="jw-job-search" name="title" maxlength="120"
                    placeholder="e.g. Customer Service" autocomplete="off">
                <button type="submit">Search Jobs <span aria-hidden="true">→</span></button>
            </form>
            <p style="font-size:13px;line-height:1.55;margin:21px 0 0">
                Browse real vacancies and apply securely through your account.
                Job availability and application deadlines vary by listing.
            </p>
        </div>
    </section>

    <section class="jw-section" aria-labelledby="jw-jobs-title">
        <div class="jw-section-header">
            <div>
                <h2 id="jw-jobs-title">Recently published jobs</h2>
                <p class="jw-subtext">Open positions from the existing job portal database.</p>
            </div>
            <a href="{{ route('search-jobs') }}" class="jw-button" style="background:#176c94;color:#fff!important">View all jobs →</a>
        </div>
        @if(($latestJobs ?? collect())->isNotEmpty())
            <div class="jw-grid">
                @foreach($latestJobs as $job)
                    <article class="jw-job">
                        <span class="jw-tag">Open opportunity</span>
                        <h3><a href="{{ route('job-details', $job->id) }}">{{ $job->title }}</a></h3>
                        <p>{{ $job->company?->company_name ?? 'Hiring employer' }}</p>
                        @if($job->country?->name)
                            <p>Location: {{ $job->country->name }}</p>
                        @endif
                        <div class="jw-bottom">
                            <a href="{{ route('job-details', $job->id) }}">View job details <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <p class="jw-empty">No currently published positions to display. Explore all opportunities or return later for newly listed jobs.</p>
        @endif
    </section>

    <section class="jw-section" aria-labelledby="jw-process-title">
        <div class="jw-section-header">
            <div>
                <h2 id="jw-process-title">Your recruitment workspace</h2>
                <p class="jw-subtext">Account tools for candidates, employers and authorized support teams.</p>
            </div>
        </div>
        <div class="jw-grid">
            <div class="jw-feature">
                <strong>01 · Find and apply</strong>
                <p>Use the current job listings, create an account and apply using the site's existing application flow.</p>
            </div>
            <div class="jw-feature">
                <strong>02 · Track progress</strong>
                <p>Applicants can review existing application information and uploaded documents in their account.</p>
            </div>
            <div class="jw-feature">
                <strong>03 · Get support</strong>
                <p>Contact the team through established channels. Private support features are shown to eligible accounts when enabled.</p>
            </div>
        </div>
    </section>

    <div class="jw-home-footer">
        <span>© {{ date('Y') }} Join Gallagher. All rights reserved.</span>
        <nav aria-label="Footer links">
            <a href="{{ route('home') }}">Home</a>
            <span aria-hidden="true"> · </span>
            <a href="{{ route('contact') }}">Contact</a>
            <span aria-hidden="true"> · </span>
            <a href="{{ route('search-jobs') }}">Find Jobs</a>
        </nav>
    </div>
</div>
