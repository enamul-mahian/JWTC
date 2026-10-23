@extends('dashboard.layout.dasboardMaster')
@section('title','Chat Inbox & Schedule')
@section('content')
<section class="section dashboard"><div class="card"><div class="card-body p-4">
<h1 class="h4">Chat inbox &amp; support schedule</h1><p>First-party support chat. Visitor contact details and messages stay private.</p>
<a href="{{ route('admin.site-chat') }}" class="btn btn-outline-primary mb-3">Refresh inbox</a>
@if(session('success'))<p class="alert alert-success">{{ session('success') }}</p>@endif
@if($errors->any())<p class="alert alert-danger">{{ $errors->first() }}</p>@endif
<form id="site-chat-settings" data-heartbeat="{{ route('admin.site-chat.presence') }}" method="POST" action="{{ route('admin.site-chat.settings') }}" class="mb-4">@csrf
<input type="hidden" name="enabled" value="0"><label class="mr-3"><input type="checkbox" name="enabled" value="1" @checked($settings->enabled)> Show chatbox</label>
<input type="hidden" name="available" value="0"><label><input type="checkbox" name="available" value="1" @checked($settings->online_until && now()->lt($settings->online_until))> I am available while this inbox is open</label>
<div class="mt-3"><input type="hidden" name="schedule_enabled" value="0"><label><input type="checkbox" name="schedule_enabled" value="1" @checked(old('schedule_enabled',$settings->schedule_enabled??false))> Limit live availability to these support hours</label></div>
<label class="d-block mt-3" for="chat-timezone">Support timezone</label><select id="chat-timezone" name="timezone" class="form-control mb-3">@foreach(\DateTimeZone::listIdentifiers() as $timezone)<option value="{{ $timezone }}" @selected(old('timezone',$settings->timezone??'UTC')===$timezone)>{{ $timezone }}</option>@endforeach</select>
@php($hours=json_decode($settings->weekly_hours??'[]',true)??[])
<div class="table-responsive"><table class="table"><thead><tr><th>Day</th><th>Opens</th><th>Closes</th></tr></thead><tbody>@foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $day=>$label)<tr><th>{{ $label }}</th><td><input type="time" class="form-control" aria-label="{{ $label }} opens" name="hours[{{ $day }}][start]" value="{{ old('hours.'.$day.'.start',$hours[$day]['start']??'') }}"></td><td><input type="time" class="form-control" aria-label="{{ $label }} closes" name="hours[{{ $day }}][end]" value="{{ old('hours.'.$day.'.end',$hours[$day]['end']??'') }}"></td></tr>@endforeach</tbody></table></div>
<small class="d-block mb-3">Leave both times blank for closed days. Overnight hours are supported. Online status requires an available operator with this inbox open; offline messages are still accepted outside support hours. Unsaved hours are never applied by the presence heartbeat.</small>
<button class="btn btn-primary">Save chat settings</button></form>
<div class="row"><div class="col-lg-5"><h2 class="h5">Conversations</h2><ul class="list-group mb-3">
@forelse($threads as $thread)<li class="list-group-item"><a href="{{ route('admin.site-chat',['thread'=>$thread->public_id]) }}">{{ $thread->name }}</a><div>{{ $thread->email }}</div><small>{{ \Illuminate\Support\Carbon::parse($thread->last_message_at,config('app.timezone'))->timezone($settings->timezone??'UTC')->format('d M Y H:i') }} {{ $settings->timezone??'UTC' }}</small></li>@empty<li class="list-group-item">No visitor conversations yet.</li>@endforelse
</ul>{{ $threads->withQueryString()->links() }}</div><div class="col-lg-7">
@if($selected)<h2 class="h5">Conversation</h2><div id="site-chat-admin-messages" role="log" aria-live="polite" style="max-height:450px;overflow:auto">
@foreach($messages as $message)<div class="border rounded p-3 mb-2" data-message-id="{{ $message['id'] }}"><strong>{{ $message['from']==='You'?'Visitor':'Support' }}</strong><p style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $message['body'] }}</p><small>{{ \Illuminate\Support\Carbon::parse($message['created_at'])->timezone($settings->timezone??'UTC')->format('d M Y H:i') }} {{ $settings->timezone??'UTC' }}</small></div>@endforeach</div>
<form method="POST" action="{{ route('admin.site-chat.reply',$selected->public_id) }}">@csrf<label for="chat-reply">Reply</label><textarea class="form-control mb-2" id="chat-reply" name="body" rows="4" maxlength="2000" required></textarea><button class="btn btn-primary">Send reply</button></form>
@else<p>Select a conversation to read messages and reply.</p>@endif
</div></div></div></div></section>
@endsection
@push('script')
<script defer src="{{ asset('site-chat/admin.js') }}?v=20261023-2"></script>
@endpush
