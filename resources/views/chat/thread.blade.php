@extends('secure.layout')
@section('title','Private Job Application Chat')
@section('content')
    <a href="{{ route('application-chat.index') }}" class="back">&larr; My conversations</a>
    <h1>Private conversation</h1>
    <p class="muted">Application #{{ $application->id }} · {{ $application->job?->title ?? 'Job application' }} · Applicant: {{ $application->user?->name ?? 'Applicant' }}</p>
    <section class="card">
        <div class="row" style="justify-content:space-between">
            <div>
                <h2>Messages</h2>
                <p class="small muted">Status: <strong id="chat-status">{{ ucfirst($thread->status) }}</strong>
                    · Unread: <strong id="chat-unread">{{ $unread }}</strong>
                </p>
            </div>
            @if(
                ($actor->user_type === \App\Enum\UserType::EMPLOYER) ||
                (in_array($actor->user_type, [\App\Enum\UserType::ADMIN, \App\Enum\UserType::SUPER_ADMIN], true)
                    && $actor->can('manage-application-chat'))
            )
                <form method="POST" action="{{ route('application-chat.status',$thread->id) }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $thread->status === 'open' ? 'closed' : 'open' }}">
                    <button type="submit" class="secondary">{{ $thread->status === 'open' ? 'Close conversation' : 'Reopen conversation' }}</button>
                </form>
            @endif
        </div>
        <div id="private-chat-app"
            data-poll-url="{{ route('application-chat.poll',$thread->id) }}"
            data-history-url="{{ route('application-chat.history',$thread->id) }}"
            data-send-url="{{ route('application-chat.send',$thread->id) }}"
            data-read-url="{{ route('application-chat.read',$thread->id) }}"
            data-csrf="{{ csrf_token() }}"
            data-self-id="{{ $actor->id }}"
            data-poll-seconds="{{ config('application_chat.poll_seconds',15) }}"
            data-last-id="{{ $messages->last()['id'] ?? 0 }}">
            <button type="button" class="secondary" id="chat-load-older"
                @if($messages->count() < 40) hidden @endif>Load older messages</button>
            <ol id="chat-messages" aria-label="Private messages" aria-live="polite"
                style="list-style:none;padding:0;max-height:500px;overflow:auto;border:1px solid #dde6f1;border-radius:10px;background:#f7f9fc">
                @forelse($messages as $message)
                    <li data-message-id="{{ $message['id'] }}"
                        style="margin:12px;max-width:90%;padding:12px;border-radius:9px;white-space:pre-wrap;overflow-wrap:anywhere;background:{{ $message['sender_id'] == $actor->id ? '#e2f3fd' : '#fff' }}">
                        <div class="small muted">{{ $message['sender_name'] }} · {{ $message['created_at'] }}</div>
                        <div>{{ $message['body'] }}</div>
                    </li>
                @empty
                    <li id="chat-empty" class="empty">No messages yet. Introduce yourself and discuss this job application.</li>
                @endforelse
            </ol>

            <form method="POST" action="{{ route('application-chat.send',$thread->id) }}"
                id="chat-send-form" @if($thread->status !== 'open') hidden @endif>
                @csrf
                <label for="chat-message-body">Write a message</label>
                <textarea id="chat-message-body" name="body" required minlength="1" maxlength="4000"
                    rows="4" placeholder="Write a message relevant to this job application."></textarea>
                <div class="row" style="justify-content:space-between;margin-top:10px">
                    <p class="small muted">Text-only messages are encrypted at rest. Do not share passwords, OTP codes, or unnecessary passport details.</p>
                    <button type="submit">Send message</button>
                </div>
            </form>
            <p id="chat-closed" class="alert" @if($thread->status === 'open') hidden @endif>
                This conversation is closed. You may still review its message history.
            </p>
            <div class="row" style="margin-top:12px">
                <button id="chat-mark-read" type="button" class="secondary">Mark as read</button>
                <span id="chat-status-announcement" role="status" aria-live="polite" class="small muted">
                    New messages are checked every {{ config('application_chat.poll_seconds',15) }} seconds when this tab is open.
                </span>
            </div>
        </div>
    </section>
    <script src="{{ asset('chat/chat.js') }}" defer></script>
@endsection
