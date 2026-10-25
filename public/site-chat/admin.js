(() => {
  'use strict';
  const settings = document.getElementById('site-chat-settings');
  if (!settings) return;

  const days = Array.from(settings.querySelectorAll('[data-chat-day]'));
  const scheduleToggle = document.getElementById('jg-chat-schedule-enabled');
  const scheduleArea = document.getElementById('jg-chat-schedule');

  function updateDay(row) {
    const closed = row.querySelector('.jg-chat-closed-input').checked;
    row.classList.toggle('jg-chat-row-closed', closed);
    row.querySelectorAll('input[type="time"]').forEach(input => {
      input.disabled = closed;
      // Keep the previously entered hours for easy reopening, but never
      // submit disabled hours from an explicitly closed day.
    });
  }
  days.forEach(row => {
    row.querySelector('.jg-chat-closed-input').addEventListener('change', () => updateDay(row));
    updateDay(row);
  });

  if (scheduleToggle && scheduleArea) {
    const toggleSchedule = () => {
      scheduleArea.classList.toggle('jg-chat-schedule-inactive', !scheduleToggle.checked);
    };
    scheduleToggle.addEventListener('change', toggleSchedule);
    toggleSchedule();
  }

  settings.addEventListener('submit', () => {
    const button = settings.querySelector('button[type="submit"]');
    if (button) {
      button.disabled = true;
      button.textContent = 'Saving…';
    }
  });

  const available = settings.querySelector('[name="available"][type="checkbox"]');
  const enabled = settings.querySelector('[name="enabled"][type="checkbox"]');
  const csrf = settings.querySelector('[name="_token"]');

  // Heartbeats only update the operator's transient presence. Weekly hours
  // are never written unless Save chat settings is submitted.
  setInterval(async () => {
    if (document.hidden || !available?.checked || !enabled?.checked || !csrf) return;
    try {
      const body = new FormData();
      body.set('_token', csrf.value);
      body.set('available', '1');
      await fetch(settings.dataset.heartbeat, {
        method: 'POST', body, credentials: 'same-origin',
        headers: { Accept: 'application/json' }
      });
    } catch {
      // Online availability automatically expires if the heartbeat fails.
    }
  }, 40000);

  const output = document.getElementById('site-chat-admin-messages');
  if (!output) return;
  let last = Math.max(0, ...Array.from(
    output.querySelectorAll('[data-message-id]'), el => Number(el.dataset.messageId)
  ));
  const zone = document.getElementById('chat-timezone')?.value || 'UTC';
  let formatTime;
  try {
    formatTime = new Intl.DateTimeFormat('en-GB', {
      timeZone: zone, day: '2-digit', month: 'short', year: 'numeric',
      hour: '2-digit', minute: '2-digit', hour12: true
    });
  } catch { formatTime = null; }

  setInterval(async () => {
    if (document.hidden) return;
    try {
      const url = new URL(window.location.href);
      url.searchParams.set('after', String(last));
      const response = await fetch(url, {
        headers: { Accept: 'application/json' }, credentials: 'same-origin'
      });
      if (!response.ok) return;
      const data = await response.json();
      if (!Array.isArray(data.messages)) return;
      for (const row of data.messages) {
        if (row.id <= last) continue;
        last = row.id;
        const message = document.createElement('article');
        message.className = 'jg-chat-message';
        if (row.from !== 'You') message.classList.add('jg-chat-message-support');
        message.dataset.messageId = String(row.id);
        const label = document.createElement('strong');
        label.textContent = row.from === 'You' ? 'Visitor' : 'Support';
        const body = document.createElement('p');
        body.textContent = row.body;
        const when = document.createElement('small');
        if (row.created_at) {
          const date = new Date(row.created_at);
          when.textContent = formatTime && !Number.isNaN(date.valueOf())
            ? formatTime.format(date)
            : '';
        }
        message.append(label, body, when);
        output.append(message);
        output.scrollTop = output.scrollHeight;
      }
    } catch {
      // Polling retries when this tab becomes active again.
    }
  }, 10000);
})();
