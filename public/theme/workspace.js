(() => {
'use strict';
const mobile = window.matchMedia('(max-width: 850px)');
document.querySelectorAll('.jg-account-menu').forEach(menu => {
  const sync = () => { menu.open = !mobile.matches; };
  sync();
  mobile.addEventListener('change', sync);
});
// Legacy scripts may add classes; the server-supplied account route is authoritative.
function syncActiveLinks() { document.querySelectorAll('.jg-profile-nav .list-item a').forEach(link => {
  const active = new URL(link.href, location.href).pathname.replace(/\/$/, '') === location.pathname.replace(/\/$/, '')
    || (link.classList.contains('active') && location.pathname.includes('/applications/'));
  link.classList.toggle('active', active);
  if (active) link.setAttribute('aria-current', 'page'); else link.removeAttribute('aria-current');
}); }
syncActiveLinks();
document.addEventListener('DOMContentLoaded', syncActiveLinks);
})();
