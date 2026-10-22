@extends('secure.layout')
@section('title', 'Document Revision History')
@section('content')
    <a class="back" href="{{ route('jobseeker.private-documents.index') }}">&larr; All my documents</a>
    <h1>{{ $document->title }}</h1>
    <div class="row">
        <span class="badge">{{ ucfirst(str_replace('_', ' ', $document->status)) }}</span>
        <span class="muted small">{{ ucfirst($document->type) }} document</span>
        @if($document->locked_at)<span class="badge">Locked by reviewer</span>@endif
    </div>
    @if ($document->locked_at)
        <div class="alert" role="status">This document is locked. To request a change, contact the support team. Its existing revisions are still available to download.</div>
    @else
        <section class="card" aria-labelledby="revise-title">
            <h2 id="revise-title">Upload a new revision</h2>
            <form action="{{ route('jobseeker.private-documents.revise', $document->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid">
                    <div>
                        <label for="title">Document title</label>
                        <input id="title" name="title" type="text" value="{{ $document->title }}" minlength="2" maxlength="180">
                    </div>
                    <div>
                        <label for="file">Replacement file</label>
                        <input id="file" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp" required>
                    </div>
                </div>
                <p class="small muted">Maximum 10 MB. Uploading a revision keeps the previous versions for your history and resets review status to pending.</p>
                <button type="submit">Submit revision</button>
            </form>
        </section>
    @endif

    <section class="card">
        <h2>Stored versions</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Version</th><th>Filename</th><th>Uploaded</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($versions as $version)
                    <tr>
                        <td>#{{ $version->version }}</td>
                        <td>{{ $version->original_name }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($version->created_at)->format('d M Y, H:i') }}</td>
                        <td><a href="{{ route('jobseeker.private-documents.download', [$document->id, $version->version]) }}">Download securely</a></td>
                    </tr>
                @empty<tr><td colspan="4">No versions found.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card">
        <h2>Review and revision history</h2>
        <ol class="timeline">
            @forelse($events as $event)
                <li>
                    <strong>{{ ucfirst(str_replace('_', ' ', $event->action)) }}</strong>
                    <div class="small muted">{{ \Illuminate\Support\Carbon::parse($event->created_at)->format('d M Y, H:i') }}</div>
                    @if($event->version)<div class="small">Version {{ $event->version }}</div>@endif
                    @if($event->reason)<p>{{ $event->reason }}</p>@endif
                </li>
            @empty<li>No activity recorded yet.</li>@endforelse
        </ol>
    </section>
@endsection
