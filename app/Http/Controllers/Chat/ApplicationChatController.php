<?php

namespace App\Http\Controllers\Chat;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\ApplicationChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ApplicationChatController extends Controller
{
    private function actor(Request $request, ApplicationChat $chat)
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless(in_array($user->user_type, [
            UserType::JOB_SEEKER, UserType::EMPLOYER,
            UserType::ADMIN, UserType::SUPER_ADMIN,
        ], true), 403);
        if (in_array($user->user_type, [UserType::ADMIN, UserType::SUPER_ADMIN], true)) {
            abort_unless($user->can('manage-application-chat'), 403);
        }
        abort_unless(config('application_chat.enabled'), 404);
        abort_unless($chat->ready(), 503);

        return $user;
    }

    private function wantsJson(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    public function index(Request $request, ApplicationChat $chat)
    {
        $actor = $this->actor($request, $chat);
        $applications = $chat->applicantConversations($actor);
        $applicationIds = $applications->getCollection()->pluck('id')->all();
        $threads = DB::table('application_chat_threads')
            ->whereIn('application_id', $applicationIds)->get()->keyBy('application_id');
        $available = $chat->visibleApplications($actor)
            ->whereNotIn('id', DB::table('application_chat_threads')->select('application_id'))
            ->with(['job', 'user:id,name'])->orderByDesc('id')
            ->paginate(10, ['*'], 'available_page');
        $unread = [];
        foreach ($threads as $thread) {
            $unread[$thread->id] = $chat->unread($actor, $thread);
        }

        if ($this->wantsJson($request)) {
            return response()->json([
                'conversations' => $applications->getCollection()->map(function ($application) use ($threads, $unread) {
                    $thread = $threads[$application->id] ?? null;
                    return [
                        'application_id' => $application->id,
                        'thread_id' => $thread?->id,
                        'job_title' => $application->job?->title,
                        'status' => $thread?->status,
                        'unread' => $unread[$thread?->id] ?? 0,
                    ];
                })->values(),
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'available_applications' => $available->getCollection()->map(fn ($record) => [
                    'id' => $record->id,
                    'job_title' => $record->job?->title,
                ])->values(),
            ]);
        }

        return view('chat.index', compact('applications', 'threads', 'unread', 'available'));
    }

    public function open(Request $request, int $application, ApplicationChat $chat)
    {
        $actor = $this->actor($request, $chat);
        $record = Application::findOrFail($application);
        abort_unless($chat->allowed($actor, $record), 403);

        $thread = $chat->create($actor, $record);

        return $this->wantsJson($request)
            ? response()->json(['thread_id' => $thread->id, 'status' => $thread->status], 201)
            : redirect()->route('application-chat.thread', $thread->id);
    }

    public function thread(Request $request, int $id, ApplicationChat $chat)
    {
        $actor = $this->actor($request, $chat);
        $thread = $chat->thread($actor, $id);
        $application = $chat->application($thread);
        $messages = $chat->messages($thread);
        $unread = $chat->unread($actor, $thread);

        if ($this->wantsJson($request)) {
            return response()->json([
                'thread' => [
                    'id' => $thread->id, 'application_id' => $thread->application_id,
                    'status' => $thread->status,
                ],
                'job_title' => $application->job?->title,
                'messages' => $messages, 'unread' => $unread,
            ]);
        }

        return view('chat.thread', compact(
            'thread', 'application', 'messages', 'unread', 'actor'
        ));
    }

    public function poll(Request $request, int $id, ApplicationChat $chat)
    {
        $actor = $this->actor($request, $chat);
        $data = $request->validate([
            'after' => ['sometimes', 'integer', 'min:0'],
        ]);
        $thread = $chat->thread($actor, $id);
        $messages = $chat->messages($thread, (int) ($data['after'] ?? 0));

        return response()->json([
            'messages' => $messages, 'status' => $thread->status,
            'unread' => $chat->unread($actor, $thread),
            'last_id' => $messages->last()['id'] ?? (int) ($data['after'] ?? 0),
        ]);
    }

    public function send(Request $request, int $id, ApplicationChat $chat)
    {
        $actor = $this->actor($request, $chat);
        $data = $request->validate([
            'body' => ['required', 'string', 'min:1',
                'max:' . config('application_chat.max_message_length', 4000)],
        ]);

        $sent = $chat->send($actor, $id, $data['body']);

        return $this->wantsJson($request)
            ? response()->json(['message' => $sent], 201)
            : redirect()->route('application-chat.thread', $id)
                ->with('success', 'Message sent securely.');
    }

    public function read(Request $request, int $id, ApplicationChat $chat)
    {
        $actor = $this->actor($request, $chat);
        $last = $chat->markRead($actor, $id);

        return $this->wantsJson($request)
            ? response()->json(['last_read_id' => $last])
            : redirect()->route('application-chat.thread', $id);
    }

    public function status(Request $request, int $id, ApplicationChat $chat)
    {
        $actor = $this->actor($request, $chat);
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'closed'])],
        ]);
        $thread = $chat->changeStatus($actor, $id, $data['status']);

        return $this->wantsJson($request)
            ? response()->json(['status' => $thread->status])
            : redirect()->route('application-chat.thread', $id)
                ->with('success', 'Conversation status updated.');
    }
}
