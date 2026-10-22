@extends('secure.layout')
@section('title', 'My Private Documents')
@section('content')
    <nav class="row" aria-label="Breadcrumb">
        <a href="{{ route('job-seeker-profile') }}">My dashboard</a><span aria-hidden="true">/</span><span>Secure documents</span>
    </nav>
    <h1>My private documents</h1>
    <p class="muted">Upload a passport, certificate, or other supporting document. New files are kept outside the public website and must be reviewed.</p>

    <section class="card" aria-labelledby="upload-title">
        <h2 id="upload-title">Upload a document</h2>
        <form action="{{ route('jobseeker.private-documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid">
                <div>
                    <label for="type">Document type</label>
                    <select id="type" name="type" required>
                        <option value="">Select type</option>
                        <option value="passport" @selected(old('type')==='passport')>Passport</option>
                        <option value="certificate" @selected(old('type')==='certificate')>Certificate</option>
                        <option value="other" @selected(old('type')==='other')>Other document</option>
                    </select>
                </div>
                <div>
                    <label for="title">Document title</label>
                    <input id="title" type="text" name="title" value="{{ old('title') }}" minlength="2" maxlength="180" required placeholder="e.g. Current passport">
                </div>
                <div>
                    <label for="file">File (max 10 MB)</label>
                    <input id="file" name="file" type="file" required accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp">
                </div>
            </div>
            <p class="small muted">PDF, JPG, PNG and WebP only. Only one passport record per account. To replace it, use its revision page.</p>
            <button type="submit">Upload privately</button>
        </form>
    </section>

    <section class="card" aria-labelledby="records-title">
        <h2 id="records-title">Your documents</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th scope="col">Title</th><th scope="col">Type</th><th scope="col">Review status</th><th scope="col">Updated</th><th scope="col">Action</th></tr></thead>
                <tbody>
                @forelse ($documents as $document)
                    <tr>
                        <td>{{ $document->title }}</td>
                        <td>{{ ucfirst($document->type) }}</td>
                        <td><span class="badge">{{ str_replace('_', ' ', ucfirst($document->status)) }}</span>
                            @if ($document->locked_at)<span class="small muted">(Locked)</span>@endif</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($document->updated_at)->format('d M Y') }}</td>
                        <td><a href="{{ route('jobseeker.private-documents.show', $document->id) }}">View revisions</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No private documents uploaded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($documents->hasPages())
            <nav aria-label="Document list pages" class="row">
                @if($documents->previousPageUrl())<a href="{{ $documents->previousPageUrl() }}">Previous</a>@endif
                <span class="muted">Page {{ $documents->currentPage() }} of {{ $documents->lastPage() }}</span>
                @if($documents->nextPageUrl())<a href="{{ $documents->nextPageUrl() }}">Next</a>@endif
            </nav>
        @endif
    </section>
@endsection
