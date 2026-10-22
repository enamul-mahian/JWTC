@extends('secure.layout')
@section('title', 'Manage Interviews')
@section('content')
    <h1>Online interview management</h1>
    <p class="muted">Schedule or manage interviews only for job applications you are authorized to handle.</p>
    <section class="card">
        <div class="table-wrap"><table>
            <thead><tr><th>Application</th><th>Applicant</th><th>Job</th><th>Manage</th></tr></thead>
            <tbody>
            @forelse($applications as $application)
                <tr>
                    <td>#{{ $application->id }}</td>
                    <td>{{ $application->user?->name ?? 'Applicant' }}</td>
                    <td>{{ $application->job?->title ?? 'Job application' }}</td>
                    <td><a href="{{ route('interviews.manage.show', $application->id) }}">Open schedule</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">No job applications are available for your account.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <nav aria-label="Application pages" class="row">
            @if($applications->previousPageUrl())<a href="{{ $applications->previousPageUrl() }}">Previous</a>@endif
            <span class="muted">Page {{ $applications->currentPage() }} of {{ $applications->lastPage() }}</span>
            @if($applications->nextPageUrl())<a href="{{ $applications->nextPageUrl() }}">Next</a>@endif
        </nav>
    </section>
@endsection
