// Open Ledger's service worker: the site keeps opening when the network does not.
//
// Pages are network-first and copied only as a fallback for when the network is gone —
// .htaccess is right that a page is per-user and must never be served stale while the network
// is up. Static assets are cache-first, because their URLs carry a version. POSTs are never
// touched here: the page keeps those itself (see the submit handler in views.php) and posts them
// again once it is back online. Registered from themeBootScript(), over https only, so the
// Android app's loopback server and a `php -S` on a laptop never get one.
var V = 'ol-1';
var OFFLINE = '/offline';

// Everything, and then the one page that must always be there.
function wipe() {
  return caches.delete(V).then(function () { return caches.open(V); })
    .then(function (c) { return c.add(OFFLINE); });
}

self.addEventListener('install', function (e) {
  e.waitUntil(caches.open(V).then(function (c) { return c.add(OFFLINE); }).then(function () { return self.skipWaiting(); }));
});

self.addEventListener('activate', function (e) {
  e.waitUntil(caches.keys().then(function (ks) {
    return Promise.all(ks.filter(function (k) { return k !== V; }).map(function (k) { return caches.delete(k); }));
  }).then(function () { return self.clients.claim(); }));
});

self.addEventListener('fetch', function (e) {
  var req = e.request, url = new URL(req.url);
  if (req.method !== 'GET' || url.origin !== location.origin) return;

  if (/^\/(design-tokens|assets)\/|^\/manifest\.webmanifest$/.test(url.pathname)) {
    // ponytail: one stale stylesheet is left behind per deploy (the ?v= changes); wipe() on
    // sign-out is the only sweep. Add a size cap when it ever shows up in storage figures.
    e.respondWith(caches.match(req).then(function (hit) {
      return hit || fetch(req).then(function (res) {
        if (res.ok) { var copy = res.clone(); caches.open(V).then(function (c) { c.put(req, copy); }); }
        return res;
      });
    }));
    return;
  }

  if (req.mode !== 'navigate') return;
  e.respondWith(fetch(req).then(function (res) {
    // Signing out must take the offline copies with it: a page kept for one account cannot be
    // shown to whoever signs in next on the same browser. Landing on /login — by choice or by
    // the gate's redirect — is the one signal both have in common.
    if (new URL(res.url).pathname === '/login') { wipe(); return res; }
    // A redirect is the server saying "not this page", so nothing to keep under this URL.
    if (res.ok && !res.redirected) { var copy = res.clone(); caches.open(V).then(function (c) { c.put(req, copy); }); }
    return res;
  }).catch(function () {
    return caches.match(req).then(function (hit) { return hit || caches.match(OFFLINE); });
  }));
});
