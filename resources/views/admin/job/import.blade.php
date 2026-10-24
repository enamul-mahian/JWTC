<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Import Jobs</title></head>
<body style="font-family:system-ui;max-width:960px;margin:40px auto;padding:0 20px">
<a href="{{ route('admin.dashboard') }}">← Admin Dashboard</a>
<h1>Import Jobs</h1><p><a href="{{ route('admin.custom-jobs.create') }}">Create Custom Job</a></p><p>CSV import stages records for review. No jobs are published automatically.</p>
@if($errors->any())<div role="alert" style="color:#a11">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
@if(session('success'))<p role="status">{{ session('success') }}</p>@endif
<h2>CSV Import</h2>
<p>Required columns: <code>title, company, apply_url</code>. Optional fields are listed in the <a href="https://github.com/enamul-mahian/JWTC/blob/feature/import-jobs-staging/docs/import-jobs.md">import specification</a>. Maximum 500 rows / 2MB.</p>
<form method="post" action="{{ route('admin.job-import.upload') }}" enctype="multipart/form-data">@csrf
<label for="file">CSV file</label> <input id="file" type="file" name="file" accept=".csv,text/csv" required> <button type="submit">Stage CSV for review</button></form>
<h2>Import from Link</h2><p>URL parsing is not available in this initial release. It will require approved source adapters, safe URL validation and review before publication.</p>
<h2>Recent Imports</h2>
<table border="1" cellpadding="8" cellspacing="0"><thead><tr><th>Batch</th><th>File</th><th>Total</th><th>Ready</th><th>Skipped</th></tr></thead><tbody>
@foreach($batches as $batch)<tr><td><a href="{{ route('admin.job-import.show', $batch->id) }}">#{{ $batch->id }}</a></td><td>{{ $batch->filename }}</td><td>{{ $batch->total_rows }}</td><td>{{ $batch->ready_rows }}</td><td>{{ $batch->skipped_rows }}</td></tr>@endforeach
</tbody></table>{{ $batches->links() }}
</body></html>
