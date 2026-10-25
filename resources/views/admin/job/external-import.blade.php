<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Government Job Draft Import</title></head>
<body style="font:16px system-ui;max-width:1100px;margin:2rem auto;padding:1rem">
<nav><a href="{{ route('admin.jobs.index') }}">← Manage All Jobs</a></nav>
<h1>Government jobs — import from link</h1>
<p>Paste a Work Hub search-results link or an individual vacancy link to import eligible jobs as drafts. Imported vacancies need admin review; even approval does not publish live jobs.</p>
<section style="border:1px solid #ddd;border-radius:12px;padding:20px;margin:20px 0;background:#f8fafc">
<h2 style="margin-top:0">Import directly from website link</h2>
<p>Accepts <strong>jobs.service.gov.uk</strong> search results or individual job pages. Checks recent 14-day, on-site, permanent, full-time vacancies with unexpired closing dates.</p>
<form method="POST" action="{{ route('admin.external-jobs.import-link') }}">
@csrf
<label for="source_url" style="display:block;font-weight:600;margin-bottom:8px">Paste Work Hub URL</label>
<input id="source_url" name="source_url" type="url" inputmode="url" value="{{ old('source_url') }}"
 placeholder="https://www.jobs.service.gov.uk/jobs/search?pageNumber=1" autocomplete="off"
 style="width:min(100%,760px);box-sizing:border-box;padding:12px;border:1px solid #a7b0bb;border-radius:8px"
 required>
<button type="submit" style="padding:12px 18px;margin-top:10px;background:#124fb1;color:#fff;border:0;border-radius:8px;cursor:pointer">Import jobs from link</button>
</form>
<p style="font-size:14px;color:#475569">Search links check up to 10 matching vacancies per request. Duplicate, expired and unsuitable vacancies are skipped. No live jobs or applicants are changed.</p>
</section>
<h2>Alternatively, upload CSV</h2>
<p>Optional manual CSV upload. The original draft review and publishing safeguards remain in place.</p>
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
