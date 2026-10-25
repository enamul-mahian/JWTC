<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Government Job Draft Import</title></head>
<body style="font:16px system-ui;max-width:1100px;margin:2rem auto;padding:1rem">
<nav><a href="{{ route('admin.jobs.index') }}">← Manage All Jobs</a></nav>
<h1>Government jobs — draft import</h1>
<p>CSV import and admin review only. Approval marks the staging record as reviewed; it does not publish a live job. No job is published or assigned to an employer from this page.
Review source attribution, publishing permission, and application instructions before any later publication workflow.</p>
<p>Required CSV header (in this exact order):</p>
<pre style="white-space:pre-wrap">source_url,title,company_name,location,salary,application_deadline,posted_on,workplace,employment_type,schedule,summary</pre>
<p>Dates must use YYYY-MM-DD. Workplace: onsite. Employment type: permanent. Schedule: full_time. Only the last 14 days; no expired deadlines. Up to 1000 rows per file, 2 MB maximum.</p>
@if(session('import_result')) <p role="status">{{ session('import_result') }}</p> @endif
@if($errors->any())<ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
@if(session('import_errors'))<ul>@foreach(session('import_errors') as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
<form method="POST" action="{{ route('admin.external-jobs.upload') }}" enctype="multipart/form-data">
@csrf
<label for="csv">CSV file</label><input id="csv" name="csv" type="file" accept=".csv,text/csv" required>
<button type="submit">Import drafts</button>
</form>
<h2>Staged external jobs</h2>
<table border="1" cellpadding="8" cellspacing="0"><thead><tr><th>Title</th><th>Company</th><th>Posted</th><th>Status</th><th>Source</th><th>Review</th></tr></thead><tbody>
@forelse($jobs as $job)
<tr><td>{{ $job->title }}</td><td>{{ $job->company_name }}</td><td>{{ $job->posted_on }}</td><td>{{ $job->status }}</td><td><a href="{{ $job->source_url }}" target="_blank" rel="noopener noreferrer">Original job</a></td><td>@if($job->status === 'draft')<form method="POST" action="{{ route('admin.external-jobs.review', $job->id) }}">@csrf<button type="submit" name="decision" value="approved">Approve draft</button> <button type="submit" name="decision" value="rejected">Reject</button></form>@else Reviewed @endif</td></tr>
@empty <tr><td colspan="6">No imported jobs yet.</td></tr> @endforelse
</tbody></table>
{{ $jobs->links() }}
</body></html>
