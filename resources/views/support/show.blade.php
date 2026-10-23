@extends('secure.layout')
@section('title', 'Support Ticket Details')
@section('content')
    <a href="{{ route('support.index') }}" class="back">&larr; All support tickets</a>
    <h1>{{ $ticket->ticket_number }}</h1>
    <p class="muted">{{ $ticket->subject }} · {{ ucfirst($ticket->category) }} · <strong>{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</strong> · {{ ucfirst($ticket->priority) }} priority</p>
    @if(session('warning'))<p class="alert" role="status">{{ session('warning') }}</p>@endif
    <p class="muted">Recipient: {{ !empty($ticket->recipient_company_id)?\Illuminate\Support\Facades\DB::table('companies')->where('id',$ticket->recipient_company_id)->value('company_name'):'JoinGallagher support' }}</p>
    @if(isset($deliveries) && $deliveries->isNotEmpty())<section class="card"><h2>Email delivery</h2>@foreach($deliveries as $delivery)<p>Notification: {{ $delivery->status==='accepted'?'Accepted by SMTP server':ucfirst($delivery->status) }} · Attempts: {{ $delivery->attempts }}</p>@if($canManage && $delivery->status==='failed')<form method="POST" action="{{ route('support.notification.retry',$delivery->id) }}">@csrf<button>Retry email notification</button></form>@endif @endforeach</section>@endif
    <section class="card">
        <h2>Original request</h2>
        <p style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $description }}</p>
        <p class="small muted">Opened {{ \Illuminate\Support\Carbon::parse($ticket->created_at)->format('d M Y, H:i') }} · Ticket ID: #{{ $ticket->id }}</p>
    </section>
    <section class="card">
        <h2>Conversation history</h2>
        <ol class="timeline">
        @forelse($replies as $reply)
            <li>
                <strong>{{ $reply->author_name }}</strong>
                @if($reply->internal_only)<span class="badge">Staff only</span>@endif
                <div class="small muted">{{ \Illuminate\Support\Carbon::parse($reply->created_at)->format('d M Y, H:i') }}</div>
                <p style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $reply->body }}</p>
            </li>
        @empty
            <li>No replies yet.</li>
        @endforelse
        </ol>
        <div class="row" aria-label="Reply pages">
            @if($replies->previousPageUrl())<a href="{{ $replies->previousPageUrl() }}">Previous</a>@endif
            <span class="small muted">Page {{ $replies->currentPage() }} of {{ $replies->lastPage() }}</span>
            @if($replies->nextPageUrl())<a href="{{ $replies->nextPageUrl() }}">Next</a>@endif
        </div>
        @if($ticket->status !== 'closed' || $canManage)
            <form method="POST" action="{{ route('support.reply',$ticket->id) }}">
                @csrf
                <label for="body">Write a reply</label>
                <textarea id="body" name="body" maxlength="5000" rows="4" required
                    placeholder="Type your message here.">{{ old('body') }}</textarea>
                @if($canManage)
                    <label><input type="checkbox" name="internal_only" value="1" style="width:auto">
                        Internal staff note (hidden from customer)
                    </label>
                @endif
                <button type="submit">Post reply</button>
            </form>
        @endif
    </section>

    @if($canManage)
    <section class="card">
        <h2>Agent ticket management</h2>
        <form method="POST" action="{{ route('support.assign', $ticket->id) }}">
            @csrf
            <label for="assignee-id">Assign to authorized support agent</label>
            <select id="assignee-id" name="assignee_id">
                <option value="">Unassigned</option>
                @foreach($staff as $member)
                    <option value="{{ $member->id }}" @selected((int)$ticket->assigned_to === (int)$member->id)>
                        {{ $member->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="secondary">Update assignment</button>
        </form>
        <div class="divider"></div>
        <form method="POST" action="{{ route('support.status', $ticket->id) }}">
            @csrf
            <div class="grid">
                <div><label for="ticket-status">Ticket status</label>
                    <select name="status" id="ticket-status">
                        @foreach(['open'=>'Open', 'in_progress'=>'In progress','pending_customer'=>'Awaiting customer','resolved'=>'Resolved','closed'=>'Closed'] as $code => $name)
                            <option value="{{ $code }}" @selected($ticket->status === $code)>{{ $name }}</option>
                        @endforeach
                    </select></div>
                <div><label for="ticket-priority">Priority</label>
                    <select id="ticket-priority" name="priority">
                        @foreach(['low'=>'Low', 'normal'=>'Normal', 'high'=>'High','urgent'=>'Urgent'] as $code=>$name)
                            <option value="{{ $code }}" @selected($ticket->priority === $code)>{{ $name }}</option>
                        @endforeach
                    </select></div>
            </div>
            <button type="submit">Update ticket</button>
        </form>
    </section>
    @elseif($isOwner && in_array($ticket->status, ['closed','resolved'], true))
    <section class="card">
        <h2>Need further assistance?</h2>
        <form method="POST" action="{{ route('support.status', $ticket->id) }}">
            @csrf
            <input type="hidden" name="status" value="open">
            <button type="submit" class="secondary">Reopen this ticket</button>
        </form>
    </section>
    @endif

    @if(in_array($ticket->category, ['refund','payment'], true))
    <section class="card">
        <h2>Financial refund review</h2>
        @if(!$refund)
            @if($isOwner)
                <p class="small muted">This form requests a review; no refund or card charge is executed here. Your payment details must be independently verified.</p>
                <form method="POST" action="{{ route('support.refund.request',$ticket->id) }}">
                    @csrf
                    <div class="grid">
                        <div><label for="refund-currency">Currency</label>
                        <select name="currency" id="refund-currency" required>
                            @foreach(config('support_center.currencies') as $currency)
                                <option value="{{ $currency }}">{{ $currency }}</option>
                            @endforeach
                        </select></div>
                        <div><label for="refund-amount">Requested amount</label>
                            <input type="text" inputmode="decimal" id="refund-amount" name="amount"
                                placeholder="45.00" maxlength="12" required></div>
                    </div>
                    <label for="payment-reference">Payment/transaction reference</label>
                    <input id="payment-reference" name="payment_reference" maxlength="200" minlength="5" required
                        placeholder="Reference on your payment receipt (no card numbers)">
                    <label for="refund-reason">Reason and payment context</label>
                    <textarea id="refund-reason" name="reason" minlength="10" maxlength="5000" required
                        placeholder="Explain the amount, what was purchased and the issue."></textarea>
                    <button type="submit">Request manual refund review</button>
                </form>
            @else
                <p class="muted">No refund request has been submitted for this ticket.</p>
            @endif
        @else
            <p><strong>Review status:</strong> <span class="badge">{{ ucfirst(str_replace('_',' ',$refund->status)) }}</span></p>
            <p><strong>Requested:</strong> {{ $refund->currency }} {{ number_format($refund->amount_minor / 100, 2) }}</p>
            <p><strong>Claimed payment reference:</strong> {{ $refund->payment_reference }}</p>
            <p><strong>Reason:</strong> <span style="white-space:pre-wrap">{{ $refund->reason }}</span></p>
            @if(isset($refund->settlement_reference))
                <p><strong>Recorded external settlement:</strong> {{ $refund->settlement_reference }}</p>
            @endif
            <p class="small muted">Status reflects a reviewed case record. The platform does not automatically issue Stripe/bank refunds.</p>

            @if($canReview || $canSettle)
                <form method="POST" action="{{ route('support.refund.review',$ticket->id) }}">
                    @csrf
                    <label for="refund-action">Authorized refund action</label>
                    <select id="refund-action" name="action" required>
                        @if($canReview)
                            @if($refund->status === 'requested')
                                <option value="under_review">Begin verification</option>
                                <option value="rejected">Reject claim</option>
                            @elseif($refund->status === 'under_review')
                                <option value="approved">Approve after verification</option>
                                <option value="rejected">Reject with reason</option>
                            @endif
                        @endif
                        @if($canSettle && $refund->status === 'approved')
                            <option value="settled">Record verified external settlement</option>
                        @endif
                    </select>
                    <label for="refund-review-note">Decision reason / internal audit note</label>
                    <textarea id="refund-review-note" name="note" maxlength="3000"
                        placeholder="Record verification and review details."></textarea>
                    <label for="settlement-reference">External refund settlement reference (required to mark settled)</label>
                    <input id="settlement-reference" name="settlement_reference" maxlength="240"
                        placeholder="Verified bank or gateway refund reference">
                    <button type="submit" class="secondary">Record authorized decision</button>
                </form>
            @endif

            <h3>Refund audit history</h3>
            <ol class="timeline">
                @foreach($refundEvents as $event)
                    <li>
                        <strong>{{ ucfirst(str_replace('_', ' ', $event->action)) }}</strong>
                        <div class="small muted">{{ \Illuminate\Support\Carbon::parse($event->created_at)->format('d M Y, H:i') }}</div>
                        @if($event->note)<p>{{ $event->note }}</p>@endif
                    </li>
                @endforeach
            </ol>
        @endif
    </section>
    @endif

    @if($canManage && $audit->isNotEmpty())
        <section class="card">
            <h2>Agent audit timeline</h2>
            <ol class="timeline">
                @foreach($audit as $event)
                    <li>
                        <strong>{{ ucfirst(str_replace('_',' ',$event->action)) }}</strong>
                        <span class="small muted">{{ $event->old_status ?? 'New' }} → {{ $event->new_status }}</span>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif
@endsection
