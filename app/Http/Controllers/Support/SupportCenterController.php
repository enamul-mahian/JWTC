<?php

namespace App\Http\Controllers\Support;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Services\SupportCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupportCenterController extends Controller
{
    private function actor(Request $request, SupportCenter $center)
    {
        $actor = $request->user();
        abort_unless($actor, 401);
        abort_unless(in_array($actor->user_type, [
            UserType::JOB_SEEKER, UserType::EMPLOYER,
            UserType::ADMIN, UserType::SUPER_ADMIN,
        ], true), 403);
        $center->requireReady();

        return $actor;
    }

    private function json(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    private function ticketData(object $ticket, SupportCenter $center): array
    {
        return [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'requester_id' => $ticket->requester_id,
            'category' => $ticket->category,
            'subject' => $ticket->subject,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'description' => $center->description($ticket),
            'created_at' => $ticket->created_at,
            'updated_at' => $ticket->updated_at,
        ];
    }

    public function index(Request $request, SupportCenter $center)
    {
        $actor = $this->actor($request, $center);
        $status = $request->query('status');
        $items = $center->listFor($actor, is_string($status) ? $status : null);

        if ($this->json($request)) {
            $items->getCollection()->transform(fn ($row) => [
                'id' => $row->id, 'ticket_number' => $row->ticket_number,
                'subject' => $row->subject, 'category' => $row->category,
                'status' => $row->status, 'priority' => $row->priority,
                'updated_at' => $row->updated_at,
            ]);
            return response()->json($items);
        }
        return view('support.index', [
            'tickets' => $items,
            'canManage' => $center->canManage($actor),
            'staff' => $center->canManage($actor)
                ? \App\Models\User::whereIn('user_type', [
                    UserType::ADMIN, UserType::SUPER_ADMIN,
                ])->get(['id','name'])->filter(fn ($u) => $center->canManage($u))
                    ->values()
                : collect(),
            'canReview' => $center->canReview($actor),
        ]);
    }

    public function create(Request $request, SupportCenter $center)
    {
        $actor = $this->actor($request, $center);
        abort_unless(in_array($actor->user_type,
            [UserType::JOB_SEEKER, UserType::EMPLOYER], true), 403);
        return view('support.create');
    }

    public function store(Request $request, SupportCenter $center)
    {
        $actor = $this->actor($request, $center);
        $data = $request->validate([
            'category' => ['required', Rule::in([
                'general', 'complaint', 'payment', 'refund',
                'account', 'documents', 'technical',
            ])],
            'subject' => ['required', 'string', 'min:5', 'max:180'],
            'description' => ['required', 'string', 'min:10',
                'max:' . config('support_center.max_ticket_length', 10000)],
        ]);
        $ticket = $center->create($actor, $data);
        return $this->json($request)
            ? response()->json(['ticket' => $this->ticketData($ticket, $center)], 201)
            : redirect()->route('support.show', $ticket->id)
                ->with('success', 'Support request created.');
    }

    public function show(Request $request, int $id, SupportCenter $center)
    {
        $actor = $this->actor($request, $center);
        $ticket = $center->ticket($actor, $id);
        $replies = $center->replies($actor, $ticket);
        $refund = $center->refund($actor, $id);
        $refundEvents = $refund ? $center->refundEvents($actor, $id) : collect();
        if ($this->json($request)) {
            return response()->json([
                'ticket' => $this->ticketData($ticket, $center),
                'replies' => $replies,
                'refund' => $refund,
                'refund_events' => $refundEvents,
            ]);
        }

        return view('support.show', [
            'ticket' => $ticket,
            'description' => $center->description($ticket),
            'replies' => $replies,
            'refund' => $refund,
            'refundEvents' => $refundEvents,
            'actor' => $actor,
            'isOwner' => (int) $ticket->requester_id === (int) $actor->id,
            'canManage' => $center->canManage($actor),
            'canReview' => $center->canReview($actor),
            'canSettle' => $center->canSettle($actor),
            'audit' => $center->canManage($actor) ?
                DB::table('support_ticket_events')->where('ticket_id', $id)
                    ->orderBy('id')->get() : collect(),
        ]);
    }

    public function reply(Request $request, int $id, SupportCenter $center)
    {
        $actor = $this->actor($request, $center);
        $data = $request->validate([
            'body' => ['required', 'string', 'min:1',
                'max:' . config('support_center.max_reply_length', 5000)],
            'internal_only' => ['sometimes', 'boolean'],
        ]);
        $reply = $center->reply($actor, $id, $data['body'],
            (bool) ($data['internal_only'] ?? false));
        return $this->json($request)
            ? response()->json(['id' => $reply->id, 'message' => 'Reply recorded.'], 201)
            : redirect()->route('support.show', $id)->with('success', 'Reply saved.');
    }

    public function status(Request $request, int $id, SupportCenter $center)
    {
        $actor = $this->actor($request, $center);
        $data = $request->validate([
            'status' => ['required', Rule::in([
                'open', 'in_progress', 'pending_customer', 'resolved', 'closed',
            ])],
            'priority' => ['nullable', Rule::in(['low','normal','high','urgent'])],
        ]);
        $ticket = $center->status($actor, $id,
            $data['status'], $data['priority'] ?? null);
        return $this->json($request)
            ? response()->json(['status' => $ticket->status])
            : redirect()->route('support.show', $id)
                ->with('success', 'Ticket status updated.');
    }

    public function assign(Request $request, int $id, SupportCenter $center)
    {
        $actor = $this->actor($request, $center);
        abort_unless($center->canManage($actor), 403);
        $data = $request->validate([
            'assignee_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $ticket = $center->assign($actor, $id, isset($data['assignee_id'])
            ? (int) $data['assignee_id'] : null);
        return $this->json($request)
            ? response()->json(['assigned_to' => $ticket->assigned_to])
            : redirect()->route('support.show', $id)
                ->with('success', 'Ticket assignment updated.');
    }

    public function requestRefund(Request $request, int $id, SupportCenter $center)
    {
        $actor = $this->actor($request, $center);
        $data = $request->validate([
            'currency' => ['required', Rule::in(config('support_center.currencies'))],
            'amount' => ['required', 'string',
                'regex:/^(?:[1-9][0-9]{0,7})(?:\.[0-9]{1,2})?$/'],
            'payment_reference' => ['required', 'string', 'min:5', 'max:200'],
            'reason' => ['required', 'string', 'min:10',
                'max:' . config('support_center.max_refund_reason_length', 5000)],
        ]);
        $refund = $center->requestRefund($actor, $id, $data);
        return $this->json($request)
            ? response()->json([
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'message' => 'Refund review requested. Payment not issued.',
            ], 201)
            : redirect()->route('support.show', $id)
                ->with('success', 'Refund requested. No payment has been made.');
    }

    public function reviewRefund(Request $request, int $id, SupportCenter $center)
    {
        $actor = $this->actor($request, $center);
        $data = $request->validate([
            'action' => ['required', Rule::in([
                'under_review', 'approved', 'rejected', 'settled',
            ])],
            'note' => ['nullable', 'string', 'max:3000'],
            'settlement_reference' => ['nullable', 'string', 'max:240'],
        ]);
        $refund = $center->decideRefund(
            $actor, $id, $data['action'],
            $data['note'] ?? null, $data['settlement_reference'] ?? null
        );
        return $this->json($request)
            ? response()->json([
                'status' => $refund->status,
                'message' => $refund->status === 'settled'
                    ? 'External settlement recorded with audit.'
                    : 'Refund review decision recorded; no gateway payment issued.',
            ])
            : redirect()->route('support.show', $id)
                ->with('success', $refund->status === 'settled'
                    ? 'External payment settlement recorded.'
                    : 'Refund review status updated. No gateway payment was issued.');
    }
}
