(() => {
  'use strict';
  const settings = document.getElementById('site-chat-settings');
  if (!settings) return;
  setInterval(async () => {
    if (document.hidden || !settings.querySelector('[name="available"][type="checkbox"]').checked || !settings.querySelector('[name="enabled"][type="checkbox"]').checked) return;
    try { const body=new FormData(); body.set('_token',settings.querySelector('[name="_token"]').value); body.set('available','1'); await fetch(settings.dataset.heartbeat, {method:'POST',body,credentials:'same-origin',headers:{Accept:'application/json'}}); } catch { /* Availability expires without a successful heartbeat. */ }
  }, 40000);
  const output = document.getElementById('site-chat-admin-messages');
  if (!output) return;
  let last = Math.max(0, ...Array.from(output.querySelectorAll('[data-message-id]'), el => Number(el.dataset.messageId)));
  setInterval(async () => {
    if (document.hidden) return;
    try {
      const url = new URL(window.location.href); url.searchParams.set('after', String(last));
      const response = await fetch(url, {headers:{Accept:'application/json'},credentials:'same-origin'});
      if (!response.ok) return;
      const data = await response.json();
      for (const row of data.messages) {
        if (row.id <= last) continue;
        last = row.id;
        const entry = document.createElement('div'); entry.className = 'border rounded p-3 mb-2';
        const label = document.createElement('strong'); label.textContent = row.from === 'You' ? 'Visitor' : 'Support';
        const body = document.createElement('p'); body.style.whiteSpace = 'pre-wrap'; body.textContent = row.body;
        entry.append(label, body); output.append(entry); output.scrollTop = output.scrollHeight;
      }
    } catch { /* Retry on next visible poll. */ }
  }, 10000);
})();
