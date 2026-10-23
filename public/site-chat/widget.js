(() => {
  'use strict';
  const root = document.getElementById('jg-chat');
  if (!root) return;
  const panel = document.getElementById('jg-chat-panel');
  const launch = root.querySelector('.jg-chat-launch');
  const close = document.getElementById('jg-chat-close');
  const form = document.getElementById('jg-chat-form');
  const feedback = document.getElementById('jg-chat-feedback');
  const messages = document.getElementById('jg-chat-messages');
  const contact = document.getElementById('jg-chat-contact');
  const base = root.dataset.base;
  let thread = null, last = 0, busy = false;
  const seen = new Set();
  async function request(path, data) {
    const response = await fetch(base + path, {method: data ? 'POST' : 'GET', credentials:'same-origin', headers:{Accept:'application/json', ...(data ? {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''} : {})}, ...(data ? {body:JSON.stringify(data)} : {})});
    if (!response.ok) {
      if (response.status === 429) throw new Error('Please wait a moment before trying again.');
      if (response.status === 419) throw new Error('Your session expired. Refresh this page before sending.');
      throw new Error('Message could not be sent. Please retry or use our Contact page.');
    }
    return response.json();
  }
  function showContact(show) {
    contact.hidden = !show;
    contact.querySelectorAll('input').forEach(input => {input.required = show; input.disabled = !show;});
  }
  function render(rows) {
    rows.forEach(row => {
      if (seen.has(row.id)) return;
      seen.add(row.id); last = Math.max(last, row.id);
      const entry = document.createElement('div'); entry.className = row.from === 'Support' ? 'jg-chat-message jg-chat-staff' : 'jg-chat-message';
      const label = document.createElement('strong'); label.textContent = row.from;
      const text = document.createElement('p'); text.textContent = row.body;
      const time = document.createElement('small'); time.textContent = row.created_at + ' UTC';
      entry.append(label, text, time); messages.append(entry);
    });
    messages.scrollTop = messages.scrollHeight;
  }
  async function poll() {
    if (!thread || panel.hidden || document.hidden || busy) return;
    try {
      const data = await request('/threads/' + encodeURIComponent(thread) + '?after=' + last);
      render(data.messages);
      const closed = data.status === 'closed';
      form.querySelector('button[type="submit"]').disabled = closed;
      if (closed) feedback.textContent = 'This conversation is closed. Please contact our team for further help.';
    } catch (error) {feedback.textContent = error.message;}
  }
  launch.addEventListener('click', () => {panel.hidden = !panel.hidden; launch.setAttribute('aria-expanded', String(!panel.hidden)); if (!panel.hidden) {document.getElementById(thread ? 'jg-chat-body' : 'jg-chat-name').focus(); poll();}});
  function hide() {panel.hidden = true; launch.setAttribute('aria-expanded','false'); launch.focus();}
  close.addEventListener('click', hide);
  root.addEventListener('keydown', event => {if (event.key === 'Escape') hide();});
  form.addEventListener('submit', async event => {
    event.preventDefault(); if (busy) return; busy = true;
    const button = form.querySelector('button[type="submit"]'); button.disabled = true;
    feedback.textContent = 'Sending…';
    const body = document.getElementById('jg-chat-body');
    try {
      const data = await request(thread ? '/threads/' + encodeURIComponent(thread) + '/messages' : '/threads', thread ? {body:body.value} : {name:document.getElementById('jg-chat-name').value,email:document.getElementById('jg-chat-email').value,body:body.value});
      if (data.thread) {thread = data.thread; showContact(false); render(data.messages || []);}
      body.value = ''; feedback.textContent = 'Message saved. Keep this page open to see our reply.';
    } catch (error) {feedback.textContent = error.message;}
    finally {busy = false; button.disabled = false; await poll();}
  });
  let initialized = false;
  async function refreshState() {
    if (document.hidden) return;
    try {
      const data = await request('/state');
      root.hidden = !data.enabled;
      if (!data.enabled) {panel.hidden = true; return;}
      if (!initialized) {thread = data.thread; showContact(!thread); initialized = true;}
      document.getElementById('jg-chat-presence').textContent = data.online ? 'Our team is available' : 'We are offline. Leave a message for our team.';
    } catch { /* Preserve an open conversation during a brief network failure. */ }
  }
  refreshState();
  setInterval(refreshState, 30000);
  setInterval(poll, 10000);
})();
