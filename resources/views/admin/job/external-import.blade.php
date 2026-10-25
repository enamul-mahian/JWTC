@extends('dashboard.layout.dasboardMaster')

@section('title', 'Government Job Import')

@push('style')
<style>
    .jw-import-page { padding-top: 1.4rem; padding-bottom: 2.5rem; }
    .jw-import-page .jw-import-title { letter-spacing: -.03em; font-weight: 750; }
    .jw-import-page .jw-import-card { border: 1px solid #e5e9f0; border-radius: 15px; box-shadow: 0 5px 22px rgba(20,42,75,.045); overflow: hidden; }
    .jw-import-page .jw-import-accent { border-top: 4px solid #2563eb; }
    .jw-import-page .jw-import-note { background: #eef5ff; color: #233d66; border: 1px solid #dceafd; border-radius: 10px; padding: 12px 15px; }
    .jw-import-page .jw-import-url { min-height: 48px; font-size: 1rem; }
    .jw-import-page .jw-import-submit { min-height: 46px; }
    .jw-import-page .jw-import-table th { white-space: nowrap; }
    .jw-import-page .jw-import-table td { vertical-align: middle; }
    .jw-import-page .jw-import-table td:first-child { min-width: 170px; }
    .jw-import-page .jw-import-table td:nth-child(2) { min-width: 140px; }
    .jw-import-page .jw-import-table td:last-child { min-width: 165px; }
    .jw-import-page .jw-import-table .btn { white-space: nowrap; }
    .jw-import-page .jw-import-muted { color: #64748b; }
    .jw-import-page .jw-import-code { white-space: pre-wrap; overflow-wrap: anywhere; font-size: .82rem; }
</style>
@endpush

@section('content')
<section class="jw-import-page" aria-labelledby="jw-import-heading">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <p class="text-uppercase fw-semibold text-primary small mb-1">Jobs management</p>
            <h1 class="jw-import-title fs-4 mb-2" id="jw-import-heading">Government Job Import</h1>
            <p class="jw-import-muted mb-0">Bring eligible Work Hub vacancies into a reviewable draft list.</p>
        </div>
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-outline-secondary">
            &larr; Manage All Jobs
        </a>
    </div>

    <div class="jw-import-note mb-4" role="note">
        <strong>Review first, publish separately.</strong>
        Importing and approving only creates a reviewed draft. Publication is a separate, explicit Admin action.
        Confirm source attribution, publishing rights and application instructions before selecting Publish.
        Your existing jobs and applicants are not changed by imports.
    </div>

    @if(session('import_result'))
        <div class="alert alert-success" role="status">{{ session('import_result') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Unable to complete the request.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(session('import_errors'))
        <div class="alert alert-warning" role="alert">
            <strong>Some CSV rows were skipped:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card jw-import-card jw-import-accent h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <h2 class="fs-5 fw-bold mb-0">Import directly from a link</h2>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Recommended</span>
                    </div>
                    <p class="jw-import-muted mb-4">
                        Paste an individual job URL or a Work Hub search-results URL from
                        <strong>jobs.service.gov.uk</strong>. No CSV file needed.
                    </p>
                    <form method="POST" action="{{ route('admin.external-jobs.import-link') }}">
                        @csrf
                        <label for="source_url" class="form-label fw-semibold">Government job or search URL</label>
                        <input id="source_url" name="source_url" type="url"
                            class="form-control jw-import-url @error('source_url') is-invalid @enderror"
                            value="{{ old('source_url') }}"
                            placeholder="https://www.jobs.service.gov.uk/jobs/search?pageNumber=1"
                            autocomplete="off" maxlength="2048" required aria-describedby="jw-import-link-help">
                        <div class="form-text mt-2" id="jw-import-link-help">
                            Only recent (last 14 days), permanent, full-time, on-site vacancies with open deadlines
                            will be saved. Search URLs check up to 10 candidate vacancies per request.
                        </div>
                        <button type="submit" class="btn btn-primary jw-import-submit px-4 mt-3">
                            Import Jobs From Link
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card jw-import-card h-100">
                <div class="card-body p-4">
                    <h2 class="fs-5 fw-bold mb-2">CSV import</h2>
                    <p class="jw-import-muted">Have a prepared CSV instead? The original import method is still available.</p>
                    <details>
                        <summary class="fw-semibold text-primary mb-3" style="cursor:pointer">Show CSV upload</summary>
                        <form method="POST" action="{{ route('admin.external-jobs.upload') }}" enctype="multipart/form-data">
                            @csrf
                            <label for="csv" class="form-label fw-semibold">CSV file</label>
                            <input id="csv" name="csv" type="file" accept=".csv,text/csv" class="form-control" required>
                            <div class="form-text">2 MB maximum and up to 1,000 rows per upload.</div>
                            <button type="submit" class="btn btn-outline-primary mt-3">Import CSV Drafts</button>
                        </form>
                        <p class="small fw-semibold mt-4 mb-2">Required headers, in order:</p>
                        <code class="d-block p-2 bg-light rounded jw-import-code">source_url,title,company_name,location,salary,application_deadline,posted_on,workplace,employment_type,schedule,summary</code>
                        <p class="small jw-import-muted mt-2 mb-0">Dates: YYYY-MM-DD · Workplace: onsite · Employment: permanent · Schedule: full_time</p>
                    </details>
                </div>
            </div>
        </div>
    </div>

    <div class="card jw-import-card">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-4 border-bottom">
                <div>
                    <h2 class="fs-5 fw-bold mb-1">Imported Jobs &amp; Review</h2>
                    <p class="jw-import-muted small mb-0">Review imported vacancies, then explicitly publish approved drafts to the public government jobs board.</p>
                </div>
                <span class="badge bg-light text-dark border">{{ $jobs->total() }} total records</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover jw-import-table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Job title</th>
                            <th scope="col">Company</th>
                            <th scope="col">Posted</th>
                            <th scope="col">Status</th>
                            <th scope="col">Source</th>
                            <th scope="col">Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $job)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $job->title }}</td>
                                <td>{{ $job->company_name }}</td>
                                <td>{{ $job->posted_on }}</td>
                                <td>
                                    @if($job->status === 'approved')
                                        <span class="badge bg-success-subtle text-success">Approved draft</span>
                                    @elseif($job->status === 'published')
                                        <span class="badge bg-primary-subtle text-primary">Published</span>
                                    @elseif($job->status === 'rejected')
                                        <span class="badge bg-danger-subtle text-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Pending review</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ $job->source_url }}" target="_blank" rel="noopener noreferrer" class="link-primary">
                                        View source <span aria-hidden="true">&nearr;</span>
                                    </a>
                                </td>
                                <td>
                                    @if($job->status === 'draft')
                                        <form method="POST" action="{{ route('admin.external-jobs.review', $job->id) }}" class="d-flex flex-wrap gap-2">
                                            @csrf
                                            <button type="submit" name="decision" value="approved" class="btn btn-sm btn-outline-success">Approve</button>
                                            <button type="submit" name="decision" value="rejected" class="btn btn-sm btn-outline-danger">Reject</button>
                                        </form>
                                    @elseif($job->status === 'approved')
                                        <form method="POST" action="{{ route('admin.external-jobs.publication',$job->id) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="publish">
                                            <label class="small d-block mb-2"><input type="checkbox" name="publishing_rights" value="1" required> Source and posting rights checked</label>
                                            <button type="submit" class="btn btn-sm btn-primary">Publish to public board</button>
                                        </form>
                                    @elseif($job->status === 'published')
                                        <div class="d-flex flex-column gap-2">
                                            <a href="{{ route('government-jobs.show',$job->id) }}" target="_blank" rel="noopener noreferrer">Public vacancy</a>
                                            <form method="POST" action="{{ route('admin.external-jobs.publication',$job->id) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="unpublish">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Unpublish</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="jw-import-muted small">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center p-5">
                                    <div class="fs-5 fw-semibold mb-2">No imported jobs yet</div>
                                    <p class="jw-import-muted mb-0">Paste a Work Hub job or search link above to start collecting drafts.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($jobs->hasPages())
                <div class="p-3 border-top">{{ $jobs->links() }}</div>
            @endif
        </div>
    </div>
</section>
@endsection
