@extends('secure.layout')
@section('title', 'Support & Refund Center')
@section('content')
    @if(in_array(auth()->user()?->user_type,[\App\Enum\UserType::ADMIN,\App\Enum\UserType::SUPER_ADMIN],true))<a href="{{ route('admin.dashboard') }}">← Admin dashboard</a>@endif
    <h1>Support, Complaints & Refund Center</h1>
    <p class="muted">Private account support requests and payment-review records. All refund decisions are audited; this portal never transfers money automatically.</p>
    <div class="row">
        @if(in_array(auth()->user()?->user_type, [\App\Enum\UserType::JOB_SEEKER, \App\Enum\UserType::EMPLOYER], true))
            <a href="{{ route('support.create') }}" class="button">+ New support ticket</a><a href="{{ route('support.create',['category'=>'refund']) }}" class="button secondary">Request refund</a>
        @endif
        @if($canManage || $canReview)
            <span class="badge">Authorized support team</span>
        @endif
    </div>
    <section class="card">
        <h2>Ticket dashboard</h2>
        <div class="row" style="margin-bottom:12px">
            @foreach([''=>'All', 'open'=>'Open', 'in_progress'=>'In progress', 'pending_customer'=>'Awaiting customer', 'resolved'=>'Resolved','closed'=>'Closed'] as $code => $title)
                <a href="{{ route('support.index', $code === '' ? [] : ['status'=>$code]) }}">{{ $title }}</a>
            @endforeach
        </div>
        <div class="table-wrap"><table>
            <thead><tr><th>Ticket</th><th>Subject</th><th>Type</th><th>Priority</th><th>Status</th><th>Updated</th></tr></thead>
            <tbody>
            @forelse($tickets as $ticket)
                <tr>
                    <td><a href="{{ route('support.show', $ticket->id) }}">{{ $ticket->ticket_number }}</a></td>
                    <td>{{ $ticket->subject }}</td>
                    <td>{{ ucfirst($ticket->category) }}</td>
                    <td>{{ ucfirst($ticket->priority) }}</td>
                    <td><span class="badge">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span></td>
                    <td>{{ \Illuminate\Support\Carbon::parse($ticket->updated_at)->format('d M Y, H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">No tickets found for this account and filter.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <nav class="row" aria-label="Ticket pages">
            @if($tickets->previousPageUrl())<a href="{{ $tickets->previousPageUrl() }}">Previous</a>@endif
            <span class="small muted">Page {{ $tickets->currentPage() }} of {{ $tickets->lastPage() }}</span>
            @if($tickets->nextPageUrl())<a href="{{ $tickets->nextPageUrl() }}">Next</a>@endif
        </nav>
    </section>
    <section class="card">
        <h2>Refund requests: important information</h2>
        <p>Requesting a refund starts a <strong>manual verification and review</strong>. Approval is not proof of a completed payment. An authorized officer must verify the transaction, follow the payment provider's refund process and independently record the settlement reference.</p>
        <p class="small muted">Never send your card PIN, full card number, account passwords or OTP in a support ticket.</p>
    </section>
@endsection
