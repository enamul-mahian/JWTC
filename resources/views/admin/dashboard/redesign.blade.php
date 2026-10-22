@extends('dashboard.layout.dasboardMaster')
@section('title', 'Overview')
@section('content')
<section class="jw-admin-overview" aria-labelledby="jw-admin-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <span class="text-muted small text-uppercase fw-bold">Platform overview</span>
            <h1 class="jw-admin-title fs-8 mt-1" id="jw-admin-heading">Join Gallagher Dashboard</h1>
            <p class="text-muted mb-0">Current platform activity, sourced from your existing application database.</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('home') }}">View public website <span aria-hidden="true">↗</span></a>
    </div>
    <div class="jw-kpis">
        <article class="jw-kpi"><span>Registered Applicants</span>
            <strong>{{ number_format($metrics['job_seekers']) }}</strong><small>Job seeker accounts</small></article>
        <article class="jw-kpi"><span>Registered Employers</span>
            <strong>{{ number_format($metrics['employers']) }}</strong><small>Employer accounts</small></article>
        <article class="jw-kpi"><span>Published Jobs</span>
            <strong>{{ number_format($metrics['published_jobs']) }}</strong><small>Current published records</small></article>
        <article class="jw-kpi"><span>Job Applications</span>
            <strong>{{ number_format($metrics['applications']) }}</strong><small>Existing application records</small></article>
    </div>

    <section class="card" aria-labelledby="jw-quick-title">
        <div class="card-body">
            <h2 id="jw-quick-title" class="fw-semibold fs-6 mb-2">Manage your workspace</h2>
            <p class="text-muted">Only modules that are enabled and authorized for your account appear here.</p>
            <div class="jw-quicklinks">
                @can('job-manage')
                    <a href="{{ route('manage.job') }}">Job management <span aria-hidden="true">↗</span></a>
                @endcan
                @if(auth()->user()->can('review-private-documents'))
                    <a href="{{ route('admin.private-documents.index') }}">Document reviews <span aria-hidden="true">↗</span></a>
                @endif
                @if(auth()->user()->can('manage-interviews'))
                    <a href="{{ route('interviews.manage.index') }}">Interview management <span aria-hidden="true">↗</span></a>
                @endif
                @if(config('email_studio.enabled') && auth()->user()->can('manage-email-studio'))
                    <a href="{{ route('email-studio.index') }}">SMTP Email Studio <span aria-hidden="true">↗</span></a>
                @endif
                @if(config('application_chat.enabled') && auth()->user()->can('manage-application-chat'))
                    <a href="{{ route('application-chat.index') }}">Applicant messages <span aria-hidden="true">↗</span></a>
                @endif
                @if(config('support_center.enabled') &&
                    (auth()->user()->can('manage-support-tickets')
                    || auth()->user()->can('review-refund-requests')
                    || auth()->user()->can('confirm-refund-settlement')))
                    <a href="{{ route('support.index') }}">Support &amp; refunds <span aria-hidden="true">↗</span></a>
                @endif
            </div>
        </div>
    </section>
    <section class="card" aria-labelledby="jw-recent-jobs-title">
        <div class="card-body">
            <h2 id="jw-recent-jobs-title" class="fw-semibold fs-6 mb-1">Latest job records</h2>
            <p class="text-muted">Up to five most recently created entries from your jobs database.</p>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th scope="col">Job</th><th scope="col">Listing status</th><th scope="col">Added</th></tr></thead>
                    <tbody>
                    @forelse($recentJobs as $job)
                        <tr>
                            <td>{{ $job->title }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $job->is_publish ? 'bg-light-success text-success' : 'bg-light-secondary text-muted' }}">
                                    {{ $job->is_publish ? 'Published' : 'Draft / unpublished' }}
                                </span>
                            </td>
                            <td>{{ $job->created_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted py-3">No job records available yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <div class="jw-admin-note" role="note">
        Metrics describe application records, not payments or revenue.
        The old dashboard's sample revenue widgets have been removed from this redesigned view.
    </div>
</section>
@endsection
