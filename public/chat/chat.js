/* JWTC Native Private Chat: no third-party SDKs or unsafe HTML insertion. */
(() => {
    'use strict';

    const root = document.getElementById('private-chat-app');
    if (!root) return;

    const messageList = document.getElementById('chat-messages');
    const form = document.getElementById('chat-send-form');
    const body = document.getElementById('chat-message-body');
    const statusText = document.getElementById('chat-status');
    const unreadText = document.getElementById('chat-unread');
    const markRead = document.getElementById('chat-mark-read');
    const announcement = document.getElementById('chat-status-announcement');
    const closedNote = document.getElementById('chat-closed');
    const selfId = Number(root.dataset.selfId || 0);
    const seconds = Math.max(10, Math.min(60, Number(root.dataset.pollSeconds || 15)));
    let last = Math.max(0, Number(root.dataset.lastId || 0));
    let busy = false;

    function announce(message) {
        if (announcement) announcement.textContent = message;
    }

    function append(message, older = false) {
        const id = Number(message.id);
        if (!Number.isSafeInteger(id) || id < 1) return;
        if (messageList.querySelector('[data-message-id="' + id + '"]')) return;

        document.getElementById('chat-empty')?.remove();

        const element = document.createElement('li');
        element.dataset.messageId = String(id);
        element.style.cssText =
            'margin:12px;max-width:90%;padding:12px;border-radius:9px;' +
            'white-space:pre-wrap;overflow-wrap:anywhere;background:' +
            (Number(message.sender_id) === selfId ? '#e2f3fd' : '#fff');

        const by = document.createElement('div');
        by.className = 'small muted';
        by.textContent = String(message.sender_name || 'Participant') + ' · ' +
            String(message.created_at || '');

        const content = document.createElement('div');
        // textContent is critical: messages must never become executable HTML.
        content.textContent = String(message.body || '');
        element.append(by, content);
        if (older) {
            messageList.prepend(element);
            while (messageList.children.length > 90) messageList.lastElementChild.remove();
        } else {
            messageList.append(element);
            // Messages are paginated on the server and never removed from storage.
            while (messageList.children.length > 90) messageList.firstElementChild.remove();
            messageList.scrollTop = messageList.scrollHeight;
        }
    }

    async function request(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': root.dataset.csrf || '',
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            },
            ...options,
        });
        if (!response.ok) {
            const msg = response.status === 403 || response.status === 404
                ? 'Access to this private conversation is no longer available.'
                : 'Request failed (' + response.status + ').';
            throw new Error(msg);
        }
        return response.json();
    }

    async function refresh() {
        if (busy || document.hidden) return;
        busy = true;
        try {
            const url = new URL(root.dataset.pollUrl, window.location.origin);
            url.searchParams.set('after', String(last));
            const result = await request(url.href);
            for (const message of (result.messages || [])) {
                append(message);
                last = Math.max(last, Number(message.id) || 0);
            }

            const chatStatus = result.status === 'open' ? 'open' : 'closed';
            if (statusText) statusText.textContent = chatStatus === 'open' ? 'Open' : 'Closed';
            if (unreadText) unreadText.textContent = String(result.unread || 0);
            if (form) form.hidden = chatStatus !== 'open';
            if (closedNote) closedNote.hidden = chatStatus === 'open';
            announce('Messages synchronized.');
        } catch (error) {
            announce(error.message || 'Could not load new messages.');
        } finally {
            busy = false;
        }
    }

    const olderButton = document.getElementById('chat-load-older');
    olderButton?.addEventListener('click', async () => {
        const first = messageList.querySelector('[data-message-id]');
        if (!first) {
            olderButton.hidden = true;
            return;
        }
        const cursor = Number(first.dataset.messageId || 0);
        if (!Number.isSafeInteger(cursor) || cursor < 1) return;

        olderButton.disabled = true;
        try {
            const url = new URL(root.dataset.historyUrl, location.origin);
            url.searchParams.set('before', String(cursor));
            const result = await request(url.href);
            const initialHeight = messageList.scrollHeight;
            // Prepend in reverse so oldest-to-newest order remains correct.
            [...(result.messages || [])].reverse().forEach((m) => append(m, true));
            messageList.scrollTop += messageList.scrollHeight - initialHeight;
            olderButton.hidden = !result.has_more || !(result.messages || []).length;
            announce((result.messages || []).length + ' older messages loaded.');
        } catch (error) {
            announce(error.message || 'Could not load older messages.');
        } finally {
            olderButton.disabled = false;
        }
    });

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (form.hidden) return;
        const text = (body?.value || '').trim();
        if (!text || text.length > 4000) return;

        const button = form.querySelector('button[type=submit]');
        if (button) button.disabled = true;
        try {
            const payload = new FormData(form);
            payload.set('body', text);
            await request(root.dataset.sendUrl, {method:'POST', body: payload});
            body.value = '';
            // Catch any unseen earlier participant messages before updating
            // the cursor to the newly sent message ID.
            await refresh();
            announce('Message sent.');
        } catch (error) {
            announce(error.message || 'Unable to send message.');
        } finally {
            if (button) button.disabled = false;
        }
    });

    markRead?.addEventListener('click', async () => {
        try {
            await request(root.dataset.readUrl, {method:'POST'});
            if (unreadText) unreadText.textContent = '0';
            announce('Messages marked as read.');
        } catch (error) {
            announce(error.message || 'Read status could not be updated.');
        }
    });

    // Poll rather than requiring an additional WebSocket daemon on cPanel.
    window.setInterval(() => {
        if (!document.hidden) refresh();
    }, seconds * 1000);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refresh();
    });
    if (messageList) messageList.scrollTop = messageList.scrollHeight;
})();
