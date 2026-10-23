const test = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
function worker(online = true) {
  const listeners = {}, writes = [], stored = new Map();
  const cache = {add:async request => {writes.push(request.url);stored.set('/pwa/offline.html',new Response('Generic offline page'));},match:async key=>stored.get(key)};
  const context = {URL,Request,Response,Promise,caches:{open:async()=>cache,keys:async()=>[],delete:async()=>true},fetch:async()=>{if (!online) throw Error('offline');return new Response('Private account or message');},self:{location:{origin:'https://example.test'},addEventListener:(name,fn)=>listeners[name]=fn,skipWaiting:async()=>{},clients:{claim:async()=>{}}}};
  // Resolve the worker's one relative install request just like a browser would.
  context.Request = class extends Request {constructor(input,options){super(new URL(input,'https://example.test'),options);}};
  vm.runInNewContext(fs.readFileSync('public/sw.js','utf8'),context);
  return {listeners,writes,stored};
}
test('only the generic offline document is cached; authenticated navigation is never stored',async()=>{
 const w=worker();let installed;
 w.listeners.install({waitUntil:p=>installed=p});await installed;
 assert.deepEqual(w.writes,['https://example.test/pwa/offline.html']);
 for (const path of ['/admin/dashboard','/jobseeker/application/3','/site-chat/state','/api/me']) {
  let response;w.listeners.fetch({request:{url:'https://example.test'+path,method:'GET',mode:'navigate'},respondWith:p=>response=p});
  assert.equal(await (await response).text(),'Private account or message');
 }
 assert.equal(w.writes.length,1);
});
test('offline account navigation gets the generic fallback; APIs and mutations bypass interception',async()=>{
 const w=worker(false);let installed;w.listeners.install({waitUntil:p=>installed=p});await installed;
 let response;w.listeners.fetch({request:{url:'https://example.test/admin/dashboard',method:'GET',mode:'navigate'},respondWith:p=>response=p});
 assert.equal(await (await response).text(),'Generic offline page');
 for(const request of [{url:'https://example.test/site-chat/messages',method:'POST',mode:'cors'},{url:'https://example.test/api/me',method:'GET',mode:'cors'},{url:'https://other.test/',method:'GET',mode:'navigate'}])w.listeners.fetch({request,respondWith:()=>assert.fail('Must bypass this request')});
 assert.equal(w.writes.length,1);
});
