@extends('secure.layout')
@section('title', 'My Interviews')
@section('content')
    <a class="back" href="{{ route('applied-jobs') }}">&larr; My applications</a>
    <h1>My online interviews</h1>
    <p class="muted">Your scheduled interviews and interview responses. Meeting details are only visible to you and approved organizers.</p>
    <section class="card">
        <div class="table-wrap"><table>
            <thead><tr><th>Application</th><th>Job</th><th>Details</th></tr></thead>
            <tbody>
            @forelse($applications as $application)
                <tr>
                    <td>#{{ $application->id }}</td>
                    <td>{{ $application->job?->title ?? 'Job application' }}</td>
                    <td><a href="{{ route('interviews.applicant.show', $application->id) }}">View interview</a></td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty">No interviews have been scheduled for your applications yet.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <nav aria-label="Interview pages" class="row">
            @if($applications->previousPageUrl())<a href="{{ $applications->previousPageUrl() }}">Previous</a>@endif
            <span class="muted">Page {{ $applications->currentPage() }} of {{ $applications->lastPage() }}</span>
            @if($applications->nextPageUrl())<a href="{{ $applications->nextPageUrl() }}">Next</a>@endif
        </nav>
    </section>
@endsection
