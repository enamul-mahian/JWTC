<div id="jg-chat" data-base="{{ url('/site-chat') }}" hidden>
<button type="button" class="jg-chat-launch" aria-controls="jg-chat-panel" aria-expanded="false">Chat with us</button>
<section id="jg-chat-panel" class="jg-chat-panel" aria-label="JoinGallagher support chat" hidden>
<header><div><strong>JoinGallagher Support</strong><p id="jg-chat-presence">Leave us a message</p></div><button type="button" id="jg-chat-close" aria-label="Close chat">×</button></header>
<div id="jg-chat-messages" class="jg-chat-messages" role="log" aria-live="polite" aria-label="Conversation"></div>
<p id="jg-chat-feedback" role="status"></p>
<form id="jg-chat-form">
<div id="jg-chat-contact"><p>Leave your email so our team can identify your enquiry. Your details are private.</p><label for="jg-chat-name">Your name</label><input id="jg-chat-name" name="name" maxlength="120" required autocomplete="name"><label for="jg-chat-email">Email address</label><input id="jg-chat-email" name="email" type="email" maxlength="254" required autocomplete="email"></div>
<label for="jg-chat-body">Message</label><textarea id="jg-chat-body" name="body" rows="3" maxlength="2000" required></textarea>
<button type="submit" class="jg-button">Send message</button>
</form>
</section></div>
<link rel="stylesheet" href="{{ asset('site-chat/widget.css') }}?v=20261023-2">
<script defer src="{{ asset('site-chat/widget.js') }}?v=20261023-2"></script>
