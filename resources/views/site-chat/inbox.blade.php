@extends('dashboard.layout.dasboardMaster')
@section('title', 'Support Inbox & Opening Hours')
@push('dashboard-page-style')
    <link rel="stylesheet" href="{{ asset('site-chat/admin.css') }}?v=20261026-1">
@endpush
@section('content')
@php
    $weekly = json_decode($settings->weekly_hours ?? '{}', true);
    $weekly = is_array($weekly) ? $weekly : [];
    $days = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'];
    $hasSchedule = (bool) old('schedule_enabled', $settings->schedule_enabled ?? false);
    $zone = old('timezone', $settings->timezone ?? 'UTC');
@endphp
<div id="jg-chat-admin">
    <header class="jg-chat-page-head">
        <div>
            <span class="jg-chat-eyebrow">SUPPORT OPERATIONS</span>
            <h1>Live chat &amp; support inbox</h1>
            <p>Set when your team is available, handle visitor messages and keep conversations private.</p>
        </div>
        <div class="jg-chat-header-actions">
            <span class="jg-chat-count">{{ $threads->total() }} conversations</span>
            <a class="jg-chat-button jg-chat-button-outline" href="{{ route('admin.site-chat') }}">Refresh inbox</a>
        </div>
    </header>

    @if(session('success'))<div class="jg-chat-alert jg-chat-alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="jg-chat-alert jg-chat-alert-error" role="alert">
            <strong>Changes were not saved.</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="jg-chat-panel jg-chat-settings-panel" aria-labelledby="jg-chat-settings-title">
        <div class="jg-chat-panel-head">
            <div>
                <h2 id="jg-chat-settings-title">Availability &amp; opening hours</h2>
                <p>Closed days remain offline. Visitors can still leave messages when live support is unavailable.</p>
            </div>
            <span class="jg-chat-status-note">Timezone-based schedule</span>
        </div>

        <form id="site-chat-settings" method="POST" action="{{ route('admin.site-chat.settings') }}"
              data-heartbeat="{{ route('admin.site-chat.presence') }}">
            @csrf
            <div class="jg-chat-setting-toggles">
                <label class="jg-chat-option">
                    <input type="hidden" name="enabled" value="0">
                    <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $settings->enabled))>
                    <span><strong>Enable website chat</strong><small>Show the chat widget to visitors</small></span>
                </label>
                <label class="jg-chat-option">
                    <input type="hidden" name="available" value="0">
                    <input type="checkbox" name="available" value="1" @checked(old('available', $settings->online_until && now()->lt($settings->online_until)))>
                    <span><strong>Available now</strong><small>Remain online while this inbox stays open</small></span>
                </label>
                <label class="jg-chat-option">
                    <input type="hidden" name="schedule_enabled" value="0">
                    <input id="jg-chat-schedule-enabled" type="checkbox" name="schedule_enabled" value="1" @checked($hasSchedule)>
                    <span><strong>Use weekly opening hours</strong><small>Only show live availability during scheduled hours</small></span>
                </label>
            </div>

            <div class="jg-chat-schedule" id="jg-chat-schedule">
                <div class="jg-chat-schedule-top">
                    <div>
                        <h3>Weekly schedule</h3>
                        <p>Choose <strong>Closed</strong> for days off. Both times set to 00:00 also mean Closed.</p>
                    </div>
                    <div class="jg-chat-zone">
                        <label for="chat-timezone">Support timezone</label>
                        <select id="chat-timezone" name="timezone">
                            @foreach(\DateTimeZone::listIdentifiers() as $timezone)
                                <option value="{{ $timezone }}" @selected($zone === $timezone)>{{ $timezone }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="jg-chat-hours-scroll">
                    <table class="jg-chat-hours">
                        <thead>
                            <tr><th scope="col">Day</th><th scope="col">Availability</th><th scope="col">Opens</th><th scope="col">Closes</th></tr>
                        </thead>
                        <tbody>
                            @foreach($days as $day=>$label)
                                @php
                                    $start = old('hours.'.$day.'.start', $weekly[$day]['start'] ?? '');
                                    $end = old('hours.'.$day.'.end', $weekly[$day]['end'] ?? '');
                                    $closed = (bool) old('hours.'.$day.'.closed', !isset($weekly[$day]));
                                    $closed = $closed || ($start === '00:00' && $end === '00:00');
                                @endphp
                                <tr data-chat-day="{{ $day }}" @class(['jg-chat-row-closed'=>$closed])>
                                    <th scope="row">{{ $label }}</th>
                                    <td>
                                        <label class="jg-chat-closed-toggle">
                                            <input type="hidden" name="hours[{{ $day }}][closed]" value="0">
                                            <input type="checkbox" class="jg-chat-closed-input" name="hours[{{ $day }}][closed]" value="1" @checked($closed) aria-label="Closed on {{ $label }}">
                                            <span>Closed</span>
                                        </label>
                                    </td>
                                    <td><input class="jg-chat-time" type="time" name="hours[{{ $day }}][start]" value="{{ $start }}" aria-label="{{ $label }} opening time" @disabled($closed)></td>
                                    <td><input class="jg-chat-time" type="time" name="hours[{{ $day }}][end]" value="{{ $end }}" aria-label="{{ $label }} closing time" @disabled($closed)></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="jg-chat-help">Overnight shifts are supported, including closing after midnight. A fully closed week is allowed; visitors can still send offline messages.</p>
            </div>
            <div class="jg-chat-form-footer">
                <button class="jg-chat-button jg-chat-button-primary" type="submit">Save chat settings</button>
                <span>Changes apply only after saving.</span>
            </div>
        </form>
    </section>

    <section class="jg-chat-inbox-section" aria-labelledby="jg-chat-inbox-heading">
        <div class="jg-chat-section-head"><div><h2 id="jg-chat-inbox-heading">Conversations</h2><p>Select a visitor to review the conversation and reply.</p></div></div>
        <div class="jg-chat-inbox-grid">
            <aside class="jg-chat-panel jg-chat-thread-panel" aria-label="Visitor conversations">
                <div class="jg-chat-thread-header">Visitor conversations <span>{{ $threads->total() }}</span></div>
                <div class="jg-chat-thread-list">
                    @forelse($threads as $thread)
                        <a href="{{ route('admin.site-chat',['thread'=>$thread->public_id]) }}"
                           @class(['jg-chat-thread','jg-chat-thread-active'=>$selected && $selected->public_id===$thread->public_id])
                           @if($selected && $selected->public_id===$thread->public_id) aria-current="page" @endif>
                            <span class="jg-chat-thread-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($thread->name,0,1)) }}</span>
                            <span class="jg-chat-thread-info">
                                <strong>{{ $thread->name }}</strong>
                                <small>{{ $thread->email }}</small>
                                <small>{{ \Illuminate\Support\Carbon::parse($thread->last_message_at, config('app.timezone'))->timezone($settings->timezone ?? 'UTC')->format('d M Y, h:i A') }}</small>
                            </span>
                            <span @class(['jg-chat-thread-dot','jg-chat-thread-dot-open'=>$thread->status==='open']) aria-label="{{ $thread->status }}"></span>
                        </a>
                    @empty
                        <div class="jg-chat-no-threads">No conversations yet. New visitor messages will appear here.</div>
                    @endforelse
                </div>
                @if($threads->hasPages())<div class="jg-chat-pagination">{{ $threads->withQueryString()->links() }}</div>@endif
            </aside>
            <div class="jg-chat-panel jg-chat-conversation">
                @if($selected)
                    <div class="jg-chat-conversation-head">
                        <div>
                            <h3>Conversation</h3>
                            <span>Ref {{ substr($selected->public_id, 0, 8) }}</span>
                        </div>
                        <span @class(['jg-chat-conversation-status','jg-chat-conversation-status-open'=>$selected->status==='open'])>{{ ucfirst($selected->status) }}</span>
                    </div>
                    <div id="site-chat-admin-messages" class="jg-chat-messages" role="log" aria-live="polite" aria-label="Conversation messages">
                        @forelse($messages as $message)
                            <article @class(['jg-chat-message','jg-chat-message-support'=>$message['from']!=='You']) data-message-id="{{ $message['id'] }}">
                                <strong>{{ $message['from']==='You' ? 'Visitor' : 'Support' }}</strong>
                                <p>{{ $message['body'] }}</p>
                                <small>{{ \Illuminate\Support\Carbon::parse($message['created_at'])->timezone($settings->timezone ?? 'UTC')->format('d M Y, h:i A') }}</small>
                            </article>
                        @empty
                            <p class="jg-chat-conversation-empty">No messages in this conversation.</p>
                        @endforelse
                    </div>
                    @if($selected->status==='open')
                        <form class="jg-chat-reply" method="POST" action="{{ route('admin.site-chat.reply',$selected->public_id) }}">
                            @csrf
                            <label for="chat-reply">Reply to visitor</label>
                            <textarea id="chat-reply" name="body" rows="3" maxlength="2000" placeholder="Write a helpful reply…" required>{{ old('body') }}</textarea>
                            <div class="jg-chat-reply-actions"><span>Only this visitor can see your reply.</span><button class="jg-chat-button jg-chat-button-primary" type="submit">Send reply</button></div>
                        </form>
                    @else
                        <p class="jg-chat-closed-note">This conversation is closed. Replies are unavailable.</p>
                    @endif
                @else
                    <div class="jg-chat-placeholder">
                        <span class="jg-chat-placeholder-icon" aria-hidden="true">✉</span>
                        <h3>No conversation selected</h3>
                        <p>Choose a conversation from the left to view messages and reply privately.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
@push('script')
<script defer src="{{ asset('site-chat/admin.js') }}?v=20261026-1"></script>
@endpush
