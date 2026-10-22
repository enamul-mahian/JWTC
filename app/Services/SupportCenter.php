<?php

namespace App\Services;

use App\Enum\UserType;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SupportCenter
{
    public function ready(): bool
    {
        return Schema::hasTable('support_tickets')
            && Schema::hasTable('support_ticket_replies')
            && Schema::hasTable('support_ticket_events')
            && Schema::hasTable('support_refund_requests')
            && Schema::hasTable('support_refund_events');
    }

    public function requireReady(): void
    {
        abort_unless(config('support_center.enabled'), 404);
        abort_unless($this->ready(), 503, 'Support center migrations are not applied.');
    }

    private function isPrivileged(User $actor): bool
    {
        return in_array($actor->user_type,
            [UserType::ADMIN, UserType::SUPER_ADMIN], true);
    }

    public function canManage(User $actor): bool
    {
        return $this->isPrivileged($actor)
            && $actor->can('manage-support-tickets');
    }

    public function canReview(User $actor): bool
    {
        return $this->isPrivileged($actor)
            && $actor->can('review-refund-requests');
    }

    public function canSettle(User $actor): bool
    {
        return $this->isPrivileged($actor)
            && $actor->can('confirm-refund-settlement');
    }

    public function canSee(User $actor, object $ticket): bool
    {
        return (int) $ticket->requester_id === (int) $actor->id
            || $this->canManage($actor) || $this->canReview($actor)
            || $this->canSettle($actor);
    }

    public function ticket(User $actor, int $id): object
    {
        $this->requireReady();
        $ticket = DB::table('support_tickets')->where('id', $id)->first();
        abort_unless($ticket && $this->canSee($actor, $ticket), 404);
        return $ticket;
    }

    public function listFor(User $actor, ?string $status = null)
    {
        $this->requireReady();
        $q = DB::table('support_tickets')->orderByDesc('updated_at')
            ->orderByDesc('id');
        if (!$this->canManage($actor) && !$this->canReview($actor)
            && !$this->canSettle($actor)) {
            $q->where('requester_id', $actor->id);
        }
        if ($status && in_array($status,
            ['open', 'in_progress', 'pending_customer', 'resolved', 'closed'], true)) {
            $q->where('status', $status);
        }
        return $q->paginate(20);
    }

    public function create(User $actor, array $input): object
    {
        $this->requireReady();
        abort_unless(in_array($actor->user_type,
            [UserType::JOB_SEEKER, UserType::EMPLOYER], true), 403);

        return DB::transaction(function () use ($actor, $input) {
            // Serialize new requests per account, even on parallel POSTs.
            User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            $active = DB::table('support_tickets')
                ->where('requester_id', $actor->id)
                ->whereNotIn('status', ['closed', 'resolved'])->count();
            if ($active >= config('support_center.max_open_tickets_per_user', 20)) {
                throw ValidationException::withMessages([
                    'subject' => 'Too many active requests. Please use an existing ticket.',
                ]);
            }

            $now = now();
            $id = DB::table('support_tickets')->insertGetId([
                'ticket_number' => 'JW-' . strtoupper(Str::random(14)),
                'requester_id' => $actor->id,
                'category' => $input['category'],
                'priority' => 'normal', // User cannot self-assign emergency priority.
                'subject' => trim($input['subject']),
                'description_encrypted' => Crypt::encryptString(trim($input['description'])),
                'status' => 'open', 'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->ticketEvent($id, $actor->id, 'created', null, 'open');
            return DB::table('support_tickets')->where('id', $id)->first();
        }, 3);
    }

    public function description(object $ticket): string
    {
        return Crypt::decryptString($ticket->description_encrypted);
    }

    public function replies(User $actor, object $ticket)
    {
        $this->requireReady();
        abort_unless($this->canSee($actor, $ticket), 404);
        $q = DB::table('support_ticket_replies')
            ->join('users', 'users.id', '=', 'support_ticket_replies.author_id')
            ->where('ticket_id', $ticket->id);
        if (!$this->canManage($actor) && !$this->canReview($actor)
            && !$this->canSettle($actor)) {
            $q->where('internal_only', false);
        }
        $rows = $q->orderBy('support_ticket_replies.id')
            ->paginate(30, ['support_ticket_replies.id',
                'support_ticket_replies.author_id', 'users.name as author_name',
                'body_encrypted', 'internal_only',
                'support_ticket_replies.created_at']);
        $rows->getCollection()->transform(static function ($row) {
            $row->body = Crypt::decryptString($row->body_encrypted);
            unset($row->body_encrypted);
            return $row;
        });
        return $rows;
    }

    public function reply(User $actor, int $id, string $body, bool $internal): object
    {
        $ticket = $this->ticket($actor, $id);
        $manager = $this->canManage($actor);
        abort_unless($manager || (int) $ticket->requester_id === (int) $actor->id, 403);
        abort_if($internal && !$manager, 403);
        $body = trim($body);
        if ($body === '' || mb_strlen($body) > config('support_center.max_reply_length', 5000)) {
            throw ValidationException::withMessages(['body' => 'Please enter a shorter reply.']);
        }

        return DB::transaction(function () use ($actor, $id, $body, $internal, $manager) {
            $ticket = DB::table('support_tickets')->where('id', $id)
                ->lockForUpdate()->first();
            abort_unless($ticket, 404);
            if ($ticket->status === 'closed' && !$manager) {
                throw ValidationException::withMessages(['body' =>
                    'This ticket is closed. Reopen it before replying.']);
            }
            $now = now();
            $rid = DB::table('support_ticket_replies')->insertGetId([
                'ticket_id' => $id, 'author_id' => $actor->id,
                'body_encrypted' => Crypt::encryptString($body),
                'internal_only' => $internal, 'created_at' => $now,
            ]);
            $patch = ['last_reply_id' => $rid, 'updated_at' => $now];
            if (!$manager && $ticket->status === 'pending_customer') {
                $patch['status'] = 'open';
                $this->ticketEvent($id, $actor->id, 'customer_replied',
                    'pending_customer', 'open');
            }
            DB::table('support_tickets')->where('id', $id)->update($patch);
            return DB::table('support_ticket_replies')->where('id', $rid)->first();
        }, 3);
    }

    public function status(User $actor, int $id, string $status, ?string $priority = null): object
    {
        $ticket = $this->ticket($actor, $id);
        $manager = $this->canManage($actor);
        if (!$manager) {
            abort_unless((int) $ticket->requester_id === (int) $actor->id
                && $status === 'open' && in_array($ticket->status,
                    ['resolved', 'closed'], true), 403);
        }

        return DB::transaction(function () use ($actor, $id, $status, $priority, $manager) {
            $current = DB::table('support_tickets')->where('id', $id)
                ->lockForUpdate()->first();
            abort_unless($current, 404);
            if (!$manager && !in_array($current->status, ['resolved', 'closed'], true)) {
                abort(403);
            }
            $valid = [
                'open' => ['in_progress', 'pending_customer', 'resolved', 'closed'],
                'in_progress' => ['open', 'pending_customer', 'resolved', 'closed'],
                'pending_customer' => ['open', 'in_progress', 'resolved', 'closed'],
                'resolved' => ['open', 'closed'],
                'closed' => ['open'],
            ];
            if ($status !== $current->status
                && !in_array($status, $valid[$current->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' =>
                    'That ticket status transition is not permitted.']);
            }
            $patch = [
                'status' => $status, 'updated_at' => now(),
                'resolved_at' => $status === 'resolved' ? now() :
                    ($current->status === 'resolved' ? null : $current->resolved_at),
                'closed_at' => $status === 'closed' ? now() :
                    ($current->status === 'closed' ? null : $current->closed_at),
            ];
            if ($manager && $priority !== null) $patch['priority'] = $priority;
            DB::table('support_tickets')->where('id', $id)->update($patch);
            if ($status !== $current->status) {
                $this->ticketEvent($id, $actor->id,
                    $status === 'open' && $current->status === 'closed' ?
                        'reopened' : 'status_changed',
                    $current->status, $status);
            }
            return DB::table('support_tickets')->where('id', $id)->first();
        }, 3);
    }

    public function requestRefund(User $actor, int $ticketId, array $input): object
    {
        $ticket = $this->ticket($actor, $ticketId);
        abort_unless((int) $ticket->requester_id === (int) $actor->id, 403);
        abort_unless(in_array($ticket->category, ['payment', 'refund'], true), 422,
            'Use a Payment or Refund support ticket.');

        $amount = (string) $input['amount'];
        if (!preg_match('/^(?:[1-9][0-9]{0,7})(?:\.[0-9]{1,2})?$/', $amount)) {
            throw ValidationException::withMessages(['amount' =>
                'Enter a positive amount with no more than two decimal places.']);
        }
        [$units, $cents] = array_pad(explode('.', $amount, 2), 2, '0');
        $minor = ((int) $units * 100) + (int) str_pad($cents, 2, '0');

        return DB::transaction(function () use ($actor, $ticketId, $input, $minor) {
            DB::table('support_tickets')->where('id', $ticketId)
                ->lockForUpdate()->firstOrFail();
            if (DB::table('support_refund_requests')->where('ticket_id', $ticketId)->exists()) {
                throw ValidationException::withMessages(['amount' =>
                    'This ticket already has a refund request.']);
            }
            $now = now();
            $id = DB::table('support_refund_requests')->insertGetId([
                'ticket_id' => $ticketId, 'requester_id' => $actor->id,
                'currency' => $input['currency'], 'amount_minor' => $minor,
                'payment_reference_encrypted' =>
                    Crypt::encryptString(trim($input['payment_reference'])),
                'reason_encrypted' => Crypt::encryptString(trim($input['reason'])),
                'status' => 'requested', 'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->refundEvent($id, $actor->id, 'requested', null, 'requested', null);
            return DB::table('support_refund_requests')->where('id', $id)->first();
        }, 3);
    }

    public function refund(User $actor, int $ticketId): ?object
    {
        $this->ticket($actor, $ticketId);
        $item = DB::table('support_refund_requests')
            ->where('ticket_id', $ticketId)->first();
        if (!$item) return null;
        $item->payment_reference = Crypt::decryptString($item->payment_reference_encrypted);
        $item->reason = Crypt::decryptString($item->reason_encrypted);
        unset($item->payment_reference_encrypted, $item->reason_encrypted);
        if ($item->settlement_reference_encrypted) {
            $item->settlement_reference = Crypt::decryptString(
                $item->settlement_reference_encrypted
            );
        }
        unset($item->settlement_reference_encrypted);
        return $item;
    }

    public function refundEvents(User $actor, int $ticketId)
    {
        $refund = $this->refund($actor, $ticketId);
        if (!$refund) return collect();
        return DB::table('support_refund_events')
            ->where('refund_id', $refund->id)
            ->orderBy('id')
            ->get()->map(static function ($row) use ($actor) {
                // Internal decision reasons visible only to approved reviewers.
                $row->note = $row->note_encrypted && in_array(
                    $actor->user_type, [UserType::ADMIN, UserType::SUPER_ADMIN], true)
                    && $actor->can('review-refund-requests')
                    ? Crypt::decryptString($row->note_encrypted) : null;
                unset($row->note_encrypted);
                return $row;
            });
    }

    public function decideRefund(
        User $actor, int $ticketId, string $action,
        ?string $note = null, ?string $settlementReference = null
    ): object {
        $this->ticket($actor, $ticketId);
        if ($action === 'settled') {
            abort_unless($this->canSettle($actor), 403);
            if (!$settlementReference || mb_strlen(trim($settlementReference)) < 6) {
                throw ValidationException::withMessages([
                    'settlement_reference' => 'Enter a verified external settlement reference.',
                ]);
            }
        } else {
            abort_unless($this->canReview($actor), 403);
        }

        $transitions = [
            'under_review' => ['requested'],
            'approved' => ['under_review'],
            'rejected' => ['requested', 'under_review'],
            'settled' => ['approved'],
        ];

        return DB::transaction(function () use ($actor, $ticketId, $action,
            $note, $settlementReference, $transitions) {
            $refund = DB::table('support_refund_requests')
                ->where('ticket_id', $ticketId)->lockForUpdate()->first();
            abort_unless($refund, 404);

            if (!in_array($refund->status, $transitions[$action] ?? [], true)) {
                throw ValidationException::withMessages([
                    'action' => 'This refund transition is not permitted.',
                ]);
            }
            // Settlement must be confirmed by a DIFFERENT authorized reviewer.
            if ($action === 'settled' && (int) $refund->reviewed_by === (int) $actor->id) {
                throw ValidationException::withMessages([
                    'action' => 'Another authorized officer must confirm settlement.',
                ]);
            }
            if (in_array($action, ['approved', 'rejected'], true)
                && ($note === null || trim($note) === '')) {
                throw ValidationException::withMessages([
                    'note' => 'Record the reason for your decision.',
                ]);
            }

            $now = now();
            $updates = ['status' => $action, 'updated_at' => $now];
            if (in_array($action, ['approved', 'rejected'], true)) {
                $updates['reviewed_by'] = $actor->id;
                $updates['reviewed_at'] = $now;
            }
            if ($action === 'settled') {
                $updates['settled_by'] = $actor->id;
                $updates['settled_at'] = $now;
                $updates['settlement_reference_encrypted'] = Crypt::encryptString(
                    trim($settlementReference)
                );
            }
            DB::table('support_refund_requests')
                ->where('id', $refund->id)->update($updates);
            $this->refundEvent($refund->id, $actor->id, $action,
                $refund->status, $action, $note);
            return DB::table('support_refund_requests')->where('id', $refund->id)->first();
        }, 3);
    }

    private function ticketEvent(
        int $ticketId, int $actorId, string $action,
        ?string $from, ?string $to
    ): void {
        DB::table('support_ticket_events')->insert([
            'ticket_id' => $ticketId, 'actor_id' => $actorId,
            'action' => $action, 'old_status' => $from,
            'new_status' => $to, 'created_at' => now(),
        ]);
    }

    private function refundEvent(
        int $id, int $actor, string $action, ?string $before,
        string $after, ?string $note
    ): void {
        DB::table('support_refund_events')->insert([
            'refund_id' => $id, 'actor_id' => $actor,
            'action' => $action, 'from_status' => $before,
            'to_status' => $after,
            'note_encrypted' => $note ? Crypt::encryptString(trim($note)) : null,
            'created_at' => now(),
        ]);
    }
}
