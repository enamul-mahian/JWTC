@extends('secure.layout')
@section('title','Email Studio')
@section('content')
    <h1>JWTC Email Studio</h1>
    <p class="muted">Custom branded HTML templates, Google-friendly image layouts, and private SMTP sends with PDF attachments.</p>
    <div class="row">
        <a class="button" href="{{ route('email-studio.create') }}">New email template</a>
        <a class="button secondary" href="{{ route('admin.dashboard') }}">Back to dashboard</a>
    </div>
    <div class="alert" role="note"><strong>SMTP sender:</strong>
        {{ config('email_studio.from_address') }} ·
        Studio SMTP connection: {{ config('mail.mailers.studio_smtp.host') ? 'configured in server environment' : 'not configured' }}.
        Credentials never appear in this editor.
    </div>
    <section class="card">
        <h2>Saved templates</h2>
        <div class="table-wrap"><table>
            <thead><tr><th>Name</th><th>Email subject</th><th>Updated</th><th>Action</th></tr></thead><tbody>
            @forelse($templates as $template)
                <tr>
                    <td>{{ $template->name }}</td>
                    <td>{{ $template->subject }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($template->updated_at)->format('d M Y H:i') }}</td>
                    <td><a href="{{ route('email-studio.edit',$template->id) }}">Edit / Preview / Send</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">No templates yet. Create your first email design.</td></tr>
            @endforelse
            </tbody></table></div>
            @if($templates->hasPages())
                <div class="row" aria-label="Template navigation">
                    @if($templates->previousPageUrl())<a href="{{ $templates->previousPageUrl() }}">Previous</a>@endif
                    <span class="muted">Page {{ $templates->currentPage() }} of {{ $templates->lastPage() }}</span>
                    @if($templates->nextPageUrl())<a href="{{ $templates->nextPageUrl() }}">Next</a>@endif
                </div>
            @endif
    </section>
    <section class="card">
        <h2>Your recent sends</h2>
        <div class="table-wrap"><table>
            <thead><tr><th>Date</th><th>Recipient</th><th>Subject</th><th>SMTP result</th><th>PDF</th></tr></thead><tbody>
            @forelse($logs as $log)
                <tr><td>{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d M Y H:i') }}</td>
                <td>{{ $log->recipient }}</td><td>{{ $log->subject }}</td>
                <td><span class="badge">{{ ucfirst($log->result) }}</span></td>
                <td>{{ $log->pdf_attached ? 'Attached' : 'None' }}</td></tr>
            @empty
                <tr><td colspan="5" class="empty">No SMTP sends on your account yet.</td></tr>
            @endforelse
            </tbody></table></div>
        <p class="small muted">Accepted means your SMTP server accepted the message, not proof it reached the Gmail inbox.</p>
    </section>
@endsection
