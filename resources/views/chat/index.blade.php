@extends('secure.layout')
@section('title','My Secure Messages')
@section('content')
    <a href="{{ url('/') }}" class="back">&larr; Website home</a>
    <h1>Private messaging</h1>
    <p class="muted">Chat privately about a submitted job application. Only the applicant, verified job owner and explicitly approved support administrators can see its conversation.</p>
    <section class="card">
        <h2>My conversations</h2>
        <div class="table-wrap"><table>
            <thead><tr><th>Job application</th><th>Applicant</th><th>Status</th><th>Unread</th><th>Open</th></tr></thead>
            <tbody>
                @forelse($applications as $application)
                    @php $thread = $threads[$application->id] ?? null; @endphp
                    @if($thread)
                        <tr>
                            <td>#{{ $application->id }} — {{ $application->job?->title ?? 'Job application' }}</td>
                            <td>{{ $application->user?->name ?? 'Applicant' }}</td>
                            <td>{{ ucfirst($thread->status) }}</td>
                            <td>{{ $unread[$thread->id] ?? 0 }}</td>
                            <td><a href="{{ route('application-chat.thread',$thread->id) }}">Open chat</a></td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="5" class="empty">You have no conversations yet.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if($applications->hasPages())
            <nav class="row" aria-label="Conversation pages">
                @if($applications->previousPageUrl())<a href="{{ $applications->previousPageUrl() }}">Previous</a>@endif
                <span class="muted">Page {{ $applications->currentPage() }} of {{ $applications->lastPage() }}</span>
                @if($applications->nextPageUrl())<a href="{{ $applications->nextPageUrl() }}">Next</a>@endif
            </nav>
        @endif
    </section>
    <section class="card">
        <h2>Start a job application conversation</h2>
        <p class="small muted">Choose an application in your account. You cannot start chats about unrelated applicants or employers.</p>
        <div class="table-wrap"><table>
            <thead><tr><th>Application</th><th>Applicant</th><th>Action</th></tr></thead>
            <tbody>
                @forelse($available as $application)
                    <tr>
                        <td>#{{ $application->id }} — {{ $application->job?->title ?? 'Job application' }}</td>
                        <td>{{ $application->user?->name ?? 'Applicant' }}</td>
                        <td>
                            <form method="POST" action="{{ route('application-chat.open',$application->id) }}">
                                @csrf <button type="submit">Start private chat</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty">No new application conversations to start.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if($available->hasPages())
            <nav class="row" aria-label="Available applications pages">
                @if($available->previousPageUrl())<a href="{{ $available->previousPageUrl() }}">Previous</a>@endif
                <span class="muted">Page {{ $available->currentPage() }} of {{ $available->lastPage() }}</span>
                @if($available->nextPageUrl())<a href="{{ $available->nextPageUrl() }}">Next</a>@endif
            </nav>
        @endif
    </section>
@endsection
