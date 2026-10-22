@extends('secure.layout')
@section('title', 'Document Review')
@section('content')
    <a class="back" href="{{ route('admin.private-documents.index') }}">&larr; Review queue</a>
    <h1>{{ $document->title }}</h1>
    <p class="muted">Applicant: {{ $document->applicant_name }} · {{ ucfirst($document->type) }} · Record #{{ $document->id }}</p>
    <div class="row">
        <span class="badge">{{ ucfirst(str_replace('_', ' ', $document->status)) }}</span>
        @if($document->locked_at)<span class="badge">Locked</span>@else<span class="badge">Unlocked</span>@endif
    </div>

    <section class="card" aria-labelledby="review-title">
        <h2 id="review-title">Review decision</h2>
        <p class="small muted">Each action is recorded with your account and timestamp. Review notes are visible to the applicant, so do not enter internal or confidential remarks.</p>
        <form method="POST" action="{{ route('admin.private-documents.decide', $document->id) }}">
            @csrf
            <div class="grid">
                <div>
                    <label for="action">Action</label>
                    <select id="action" name="action" required>
                        <option value="">Select action</option>
                        @if($document->locked_at)
                            <option value="unlock">Unlock document</option>
                        @else
                            <option value="approve">Approve</option>
                            <option value="request_revision">Request revision</option>
                            <option value="reject">Reject</option>
                            <option value="lock">Lock document</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label for="reason">Reason (required except approval)</label>
                    <textarea id="reason" name="reason" maxlength="1000" placeholder="Explain the reason or requested correction">{{ old('reason') }}</textarea>
                </div>
            </div>
            <button type="submit">Record decision</button>
        </form>
    </section>

    <section class="card">
        <h2>Private versions</h2>
        <div class="table-wrap"><table>
            <thead><tr><th>Version</th><th>Filename</th><th>Size</th><th>Submitted</th><th>File</th></tr></thead>
            <tbody>
                @forelse($versions as $version)
                <tr>
                    <td>{{ $version->version }}</td>
                    <td>{{ $version->original_name }}</td>
                    <td>{{ number_format($version->file_size / 1024, 0) }} KB</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($version->created_at)->format('d M Y, H:i') }}</td>
                    <td><a href="{{ route('admin.private-documents.download', [$document->id,$version->version]) }}">Download</a></td>
                </tr>
                @empty<tr><td colspan="5">No versions available.</td></tr>@endforelse
            </tbody>
        </table></div>
    </section>

    <section class="card">
        <h2>Audited decisions</h2>
        <ol class="timeline">
            @forelse($events as $event)
                <li>
                    <strong>{{ ucfirst(str_replace('_', ' ', $event->action)) }}</strong>
                    <div class="small muted">{{ \Illuminate\Support\Carbon::parse($event->created_at)->format('d M Y, H:i') }}</div>
                    @if($event->reason)<p>{{ $event->reason }}</p>@endif
                </li>
            @empty<li>No review decisions yet.</li>@endforelse
        </ol>
    </section>
@endsection
