<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Import Preview</title></head>
<body style="font-family:system-ui;max-width:1100px;margin:40px auto;padding:0 20px">
<a href="{{ route('admin.job-import.index') }}">← Import Jobs</a>
<h1>Batch #{{ $record->id }} Preview</h1>
<p>{{ $record->total_rows }} rows · {{ $record->ready_rows }} pending review · {{ $record->skipped_rows }} skipped</p>
<p><strong>Nothing has been published.</strong> Employer mapping and publication are deliberately disabled until verified.</p>
<table border="1" cellpadding="8" cellspacing="0"><thead><tr><th>Title</th><th>Company</th><th>Location</th><th>Apply link</th><th>Status</th><th>Reason</th></tr></thead><tbody>
@foreach($rows as $row)
@php($job = json_decode($row->payload, true) ?? [])
<tr><td>{{ $job['title'] ?? '' }}</td><td>{{ $job['company'] ?? '' }}</td><td>{{ $job['location'] ?? '' }}</td><td>@if(!empty($job['apply_url']))<a href="{{ $job['apply_url'] }}" target="_blank" rel="noopener noreferrer">Original link</a>@endif</td><td>{{ $row->status }}</td><td>{{ $row->reason }}</td></tr>
@endforeach</tbody></table>{{ $rows->links() }}
</body></html>
