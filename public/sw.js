'use strict';
// Only this generic offline document is cached. Pages, accounts, chat and API data
// always use the network and can never enter this worker's cache.
const CACHE = 'jg-offline-v1';
const OFFLINE = '/pwa/offline.html';
self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE).then(cache => cache.add(new Request(OFFLINE,{cache:'reload',credentials:'omit'}))).then(() => self.skipWaiting()));
});
self.addEventListener('activate', event => {
  event.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(key => key.startsWith('jg-offline-') && key !== CACHE).map(key => caches.delete(key)))).then(() => self.clients.claim()));
});
self.addEventListener('fetch', event => {
  const request = event.request;
  if (request.method !== 'GET' || request.mode !== 'navigate' || new URL(request.url).origin !== self.location.origin) return;
  event.respondWith(fetch(request).catch(async () => (await caches.open(CACHE)).match(OFFLINE).then(response => response || new Response('You are offline. Reconnect to browse jobs.',{status:503,headers:{'Content-Type':'text/plain; charset=utf-8'}}))));
});
