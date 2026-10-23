@extends('dashboard.layout.dasboardMaster')
@section('title','Visitor Chat Inbox')
@section('content')
<section class="section dashboard"><div class="card"><div class="card-body p-4">
<h1 class="h4">Visitor chat inbox</h1><p>First-party support chat. Visitor contact details and messages stay private.</p>
<a href="{{ route('admin.site-chat') }}" class="btn btn-outline-primary mb-3">Refresh inbox</a>
@if(session('success'))<p class="alert alert-success">{{ session('success') }}</p>@endif
@if($errors->any())<p class="alert alert-danger">{{ $errors->first() }}</p>@endif
<form id="site-chat-settings" method="POST" action="{{ route('admin.site-chat.settings') }}" class="mb-4">@csrf
<input type="hidden" name="enabled" value="0"><label class="mr-3"><input type="checkbox" name="enabled" value="1" @checked($settings->enabled)> Show chatbox</label>
<input type="hidden" name="available" value="0"><label><input type="checkbox" name="available" value="1" @checked($settings->online_until && now()->lt($settings->online_until))> I am available while this inbox is open</label>
<button class="btn btn-primary btn-sm">Save availability</button><small class="d-block">Visitors see offline status when no available operator keeps this inbox open.</small></form>
<div class="row"><div class="col-lg-5"><h2 class="h5">Conversations</h2><ul class="list-group mb-3">
@forelse($threads as $thread)<li class="list-group-item"><a href="{{ route('admin.site-chat',['thread'=>$thread->public_id]) }}">{{ $thread->name }}</a><div>{{ $thread->email }}</div><small>{{ $thread->last_message_at }} UTC</small></li>@empty<li class="list-group-item">No visitor conversations yet.</li>@endforelse
</ul>{{ $threads->withQueryString()->links() }}</div><div class="col-lg-7">
@if($selected)<h2 class="h5">Conversation</h2><div id="site-chat-admin-messages" role="log" aria-live="polite" style="max-height:450px;overflow:auto">
@foreach($messages as $message)<div class="border rounded p-3 mb-2" data-message-id="{{ $message['id'] }}"><strong>{{ $message['from']==='You'?'Visitor':'Support' }}</strong><p style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $message['body'] }}</p><small>{{ $message['created_at'] }} UTC</small></div>@endforeach</div>
<form method="POST" action="{{ route('admin.site-chat.reply',$selected->public_id) }}">@csrf<label for="chat-reply">Reply</label><textarea class="form-control mb-2" id="chat-reply" name="body" rows="4" maxlength="2000" required></textarea><button class="btn btn-primary">Send reply</button></form>
@else<p>Select a conversation to read messages and reply.</p>@endif
</div></div></div></div></section>
@endsection
@push('script')
<script defer src="{{ asset('site-chat/admin.js') }}?v=20261023"></script>
@endpush
