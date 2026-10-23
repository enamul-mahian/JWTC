(() => {
  'use strict';
  const standalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  if ('serviceWorker' in navigator && location.protocol === 'https:') {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js', {scope:'/', updateViaCache:'none'}).catch(() => {}));
  }
  const panel = document.querySelector('[data-pwa-install]');
  if (!panel || standalone()) return;
  let deferred = null;
  const install = panel.querySelector('[data-pwa-action]');
  const message = panel.querySelector('[data-pwa-message]');
  const manual = document.querySelector('[data-pwa-manual]');
  const dismissed = () => {try {return Date.now() - Number(localStorage.getItem('jg-install-dismissed') || 0) < 7*86400000;} catch {return false;}};
  const mobile = () => window.matchMedia('(max-width: 900px)').matches;
  const ios = /iPad|iPhone|iPod/.test(navigator.userAgent) || (/Mac/.test(navigator.platform) && navigator.maxTouchPoints > 1);
  const eligible = !/^\/(admin|login|register|company\/(login|register)|jobseeker\/(login|register))\b/.test(location.pathname);
  function show(force = false) {
    if (standalone() || (!force && (!mobile() || !eligible || dismissed()))) return;
    install.hidden = false;
    install.textContent = deferred ? 'Install app' : 'How to install';
    message.textContent = 'Keep JoinGallagher on your home screen for quick access to jobs.';
    panel.hidden = false;
  }
  function hide(remember) {
    panel.hidden = true;
    if (remember) {try {localStorage.setItem('jg-install-dismissed',String(Date.now()));} catch {}}
  }
  window.addEventListener('beforeinstallprompt', event => {
    event.preventDefault(); deferred = event; show();
  });
  window.addEventListener('appinstalled', () => {deferred = null; hide(true);});
  panel.querySelector('[data-pwa-dismiss]').addEventListener('click', () => hide(true));
  install.addEventListener('click', async () => {
    if (deferred) {
      const prompt = deferred; deferred = null;
      try {await prompt.prompt(); const choice = await prompt.userChoice; hide(choice.outcome !== 'accepted');} catch {show(true);}
    } else {
      message.textContent = ios ? 'On iPhone or iPad: open the browser Share menu, choose Add to Home Screen, then tap Add. If shown, leave Open as Web App enabled.' : 'Open your browser menu and choose Install app or Add to Home screen. If that option is unavailable, try Chrome or Edge on Android.';
      install.hidden = true;
    }
  });
  manual?.addEventListener('click', () => {install.hidden = false; show(true); panel.querySelector('[data-pwa-action]').focus();});
  window.setTimeout(() => show(), 4500);
})();
