@extends('secure.layout')
@section('title', 'Review Applicant Documents')
@section('content')
    <a class="back" href="{{ route('admin.dashboard') }}">&larr; Admin dashboard</a>
    <h1>Private document review</h1>
    <p class="muted">Only authorized reviewers can access this workspace. Document files are never public.</p>
    <div class="card">
        <form method="GET" action="{{ route('admin.private-documents.index') }}" class="row">
            <div style="min-width:250px;max-width:330px;flex:1">
                <label for="status-filter">Filter by status</label>
                <select id="status-filter" name="status">
                    <option value="">All statuses</option>
                    @foreach(['pending_review'=>'Pending review','approved'=>'Approved','revision_requested'=>'Revision requested','rejected'=>'Rejected'] as $key=>$name)
                        <option value="{{ $key }}" @selected($filter===$key)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" style="align-self:end">Apply</button>
        </form>
    </div>
    <section class="card">
        <h2>Submitted documents</h2>
        <div class="table-wrap"><table>
            <thead><tr><th>Applicant</th><th>Document</th><th>Type</th><th>Status</th><th>Last updated</th><th>Review</th></tr></thead>
            <tbody>
            @forelse($documents as $document)
                <tr>
                    <td>{{ $document->applicant_name }}</td>
                    <td>{{ $document->title }}</td>
                    <td>{{ ucfirst($document->type) }}</td>
                    <td><span class="badge">{{ ucfirst(str_replace('_',' ',$document->status)) }}</span> @if($document->locked_at)<span class="small">Locked</span>@endif</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($document->updated_at)->format('d M Y') }}</td>
                    <td><a href="{{ route('admin.private-documents.show', $document->id) }}">Open review</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">No submissions match this filter.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        @if($documents->hasPages())
            <nav aria-label="Document review pages" class="row">
                @if($documents->previousPageUrl())<a href="{{ $documents->previousPageUrl() }}">Previous</a>@endif
                <span class="muted">Page {{ $documents->currentPage() }} of {{ $documents->lastPage() }}</span>
                @if($documents->nextPageUrl())<a href="{{ $documents->nextPageUrl() }}">Next</a>@endif
            </nav>
        @endif
    </section>
@endsection
