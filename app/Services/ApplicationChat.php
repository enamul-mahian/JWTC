<?php

namespace App\Services;

use App\Enum\UserType;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ApplicationChat
{
    public function ready(): bool
    {
        return (bool) config('application_chat.enabled')
            && Schema::hasTable('application_chat_threads')
            && Schema::hasTable('application_chat_messages')
            && Schema::hasTable('application_chat_read_states')
            && Schema::hasTable('application_chat_events');
    }

    private function requireReady(): void
    {
        abort_unless(config('application_chat.enabled'), 404);
        abort_unless($this->ready(), 503, 'Private messaging is not migrated yet.');
    }

    public function visibleApplications(User $actor): Builder
    {
        if ($actor->user_type === UserType::JOB_SEEKER) {
            return Application::query()->where('user_id', $actor->id);
        }

        if ($actor->user_type === UserType::EMPLOYER && $actor->can('post-job')) {
            return Application::query()->whereHas('job', function (Builder $jobs) use ($actor) {
                $jobs->where('user_id', $actor->id)
                    ->whereHas('company', fn (Builder $company) =>
                        $company->where('user_id', $actor->id));
            });
        }

        if (in_array($actor->user_type, [UserType::ADMIN, UserType::SUPER_ADMIN], true)
            && $actor->can('manage-application-chat')) {
            return Application::query();
        }

        return Application::query()->whereRaw('1 = 0');
    }

    public function allowed(User $actor, Application $application): bool
    {
        return $this->visibleApplications($actor)
            ->whereKey($application->id)->exists();
    }

    public function applicantConversations(User $actor)
    {
        $this->requireReady();
        $query = $this->visibleApplications($actor);

        return Application::with(['job', 'user'])
            ->whereIn('id', $query->select('id'))
            ->whereIn('id', DB::table('application_chat_threads')->select('application_id'))
            ->orderByDesc('id')
            ->paginate(20);
    }

    public function create(User $actor, Application $application): object
    {
        $this->requireReady();
        abort_unless($this->allowed($actor, $application), 403);

        return DB::transaction(function () use ($application, $actor) {
            // Serialize the first creation between applicant and employer.
            Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $thread = DB::table('application_chat_threads')
                ->where('application_id', $application->id)->first();
            if ($thread) return $thread;

            $now = now();
            $id = DB::table('application_chat_threads')->insertGetId([
                'application_id' => $application->id, 'opened_by' => $actor->id,
                'status' => 'open', 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('application_chat_events')->insert([
                'thread_id' => $id, 'actor_id' => $actor->id,
                'action' => 'opened', 'before_status' => null,
                'after_status' => 'open', 'created_at' => $now,
            ]);
            return DB::table('application_chat_threads')->where('id', $id)->first();
        }, 3);
    }

    public function thread(User $actor, int $id): object
    {
        $this->requireReady();
        $thread = DB::table('application_chat_threads')->where('id', $id)->first();
        abort_unless($thread, 404);
        // Return 404 for cross-tenant threads, not sender details.
        abort_unless($this->visibleApplications($actor)
            ->whereKey($thread->application_id)->exists(), 404);

        return $thread;
    }

    public function application(object $thread): Application
    {
        return Application::with(['job', 'user:id,name'])
            ->findOrFail($thread->application_id);
    }

    public function messages(object $thread, ?int $after = null): Collection
    {
        $this->requireReady();
        $query = DB::table('application_chat_messages')
            ->join('users', 'users.id', '=', 'application_chat_messages.sender_id')
            ->where('application_chat_messages.thread_id', $thread->id);

        if ($after !== null) {
            $rows = $query->where('application_chat_messages.id', '>', $after)
                ->orderBy('application_chat_messages.id')->limit(40)
                ->get(['application_chat_messages.id', 'sender_id',
                    'users.name as sender_name', 'body_encrypted',
                    'application_chat_messages.created_at']);
        } else {
            $rows = $query->orderByDesc('application_chat_messages.id')
                ->limit(40)
                ->get(['application_chat_messages.id', 'sender_id',
                    'users.name as sender_name', 'body_encrypted',
                    'application_chat_messages.created_at'])->reverse()->values();
        }

        return $rows->map(static function ($message) {
            return [
                'id' => (int) $message->id,
                'sender_id' => (int) $message->sender_id,
                'sender_name' => $message->sender_name,
                'body' => Crypt::decryptString($message->body_encrypted),
                'created_at' => $message->created_at,
            ];
        });
    }

    public function send(User $actor, int $id, string $body): array
    {
        $thread = $this->thread($actor, $id);
        $trimmed = trim($body);
        if ($trimmed === '' || mb_strlen($trimmed) > config('application_chat.max_message_length', 4000)) {
            throw ValidationException::withMessages([
                'body' => 'Message must be between 1 and 4,000 characters.',
            ]);
        }

        return DB::transaction(function () use ($id, $actor, $trimmed) {
            $thread = DB::table('application_chat_threads')
                ->where('id', $id)->lockForUpdate()->first();
            abort_unless($thread, 404);
            if ($thread->status !== 'open') {
                throw ValidationException::withMessages([
                    'body' => 'This conversation is closed. No messages can be sent.',
                ]);
            }

            $now = now();
            $messageId = DB::table('application_chat_messages')->insertGetId([
                'thread_id' => $id, 'sender_id' => $actor->id,
                'body_encrypted' => Crypt::encryptString($trimmed),
                'created_at' => $now,
            ]);

            DB::table('application_chat_threads')->where('id', $id)
                ->update(['last_message_at' => $now, 'updated_at' => $now]);
            DB::table('application_chat_read_states')->updateOrInsert(
                ['thread_id' => $id, 'user_id' => $actor->id],
                ['last_read_message_id' => $messageId, 'updated_at' => $now]
            );

            return [
                'id' => $messageId, 'sender_id' => $actor->id,
                'sender_name' => $actor->name, 'body' => $trimmed,
                'created_at' => $now->toDateTimeString(),
            ];
        }, 3);
    }

    public function markRead(User $actor, int $id): int
    {
        $this->thread($actor, $id);

        return DB::transaction(function () use ($actor, $id) {
            $thread = DB::table('application_chat_threads')
                ->where('id', $id)->lockForUpdate()->first();
            abort_unless($thread, 404);
            $latest = (int) (DB::table('application_chat_messages')
                ->where('thread_id', $id)->max('id') ?: 0);
            $existing = (int) (DB::table('application_chat_read_states')
                ->where('thread_id', $id)->where('user_id', $actor->id)
                ->value('last_read_message_id') ?: 0);
            $last = max($latest, $existing);
            DB::table('application_chat_read_states')->updateOrInsert(
                ['thread_id' => $id, 'user_id' => $actor->id],
                ['last_read_message_id' => $last ?: null, 'updated_at' => now()]
            );
            return $last;
        }, 3);
    }

    public function unread(User $actor, object $thread): int
    {
        $this->thread($actor, $thread->id);
        $seen = (int) (DB::table('application_chat_read_states')
            ->where('thread_id', $thread->id)->where('user_id', $actor->id)
            ->value('last_read_message_id') ?: 0);

        return DB::table('application_chat_messages')
            ->where('thread_id', $thread->id)
            ->where('id', '>', $seen)
            ->where('sender_id', '!=', $actor->id)->count();
    }

    public function changeStatus(User $actor, int $id, string $status): object
    {
        $this->requireReady();
        $thread = $this->thread($actor, $id);
        abort_unless(in_array($status, ['open', 'closed'], true), 422);

        // Closing is restricted to the job owner and explicitly approved admin.
        // Applicants retain their own chat history; they cannot silence employers.
        abort_unless(
            ($actor->user_type === UserType::EMPLOYER && $this->allowed(
                $actor, Application::findOrFail($thread->application_id)))
            || (in_array($actor->user_type,
                [UserType::ADMIN, UserType::SUPER_ADMIN], true)
                && $actor->can('manage-application-chat')), 403
        );

        return DB::transaction(function () use ($actor, $id, $status) {
            $old = DB::table('application_chat_threads')->where('id', $id)
                ->lockForUpdate()->first();
            abort_unless($old, 404);

            if ($old->status === $status) return $old;

            $now = now();
            DB::table('application_chat_threads')->where('id', $id)->update([
                'status' => $status,
                'closed_at' => $status === 'closed' ? $now : null,
                'closed_by' => $status === 'closed' ? $actor->id : null,
                'updated_at' => $now,
            ]);
            DB::table('application_chat_events')->insert([
                'thread_id' => $id, 'actor_id' => $actor->id,
                'action' => $status === 'closed' ? 'closed' : 'reopened',
                'before_status' => $old->status, 'after_status' => $status,
                'created_at' => $now,
            ]);

            return DB::table('application_chat_threads')->where('id', $id)->first();
        }, 3);
    }
}
