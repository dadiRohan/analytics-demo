(function (window, document) {
  'use strict';

  var ENDPOINT    = window.ANALYTICS_ENDPOINT || '/api/events';
  var TOKEN       = window.ANALYTICS_TOKEN    || '';
  var ANON_COOKIE = 'ajs_anonymous_id';
  var SESSION_KEY = 'ajs_session_id';
  var SESSION_TS  = 'ajs_session_ts';
  var IDLE_MS     = 30 * 60 * 1000;

  function uuid() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      var r = (Math.random() * 16) | 0;
      return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
  }

  function readCookie(name) {
    var m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : null;
  }

  function writeCookie(name, value, days) {
    var exp = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = name + '=' + encodeURIComponent(value) +
      '; expires=' + exp + '; path=/; SameSite=Lax';
  }

  function getAnonymousId() {
    var id = readCookie(ANON_COOKIE);
    if (!id) { id = uuid(); writeCookie(ANON_COOKIE, id, 365); }
    return id;
  }

  function getSessionId() {
    var id = sessionStorage.getItem(SESSION_KEY);
    var ts = parseInt(sessionStorage.getItem(SESSION_TS) || '0', 10);
    if (!id || Date.now() - ts > IDLE_MS) {
      id = uuid();
      sessionStorage.setItem(SESSION_KEY, id);
    }
    sessionStorage.setItem(SESSION_TS, Date.now().toString());
    return id;
  }

  var queue = [];
  var timer = null;

  function scheduleFlush() {
    if (timer) return;
    timer = setTimeout(function () { timer = null; flush(false); }, 2000);
  }

  function flush(sync) {
    if (!queue.length) return;
    var batch = queue.splice(0, 50);
    var body  = JSON.stringify({ events: batch });
    var url   = ENDPOINT + '?token=' + encodeURIComponent(TOKEN);

    if (sync && navigator.sendBeacon) {
      navigator.sendBeacon(url, body);
      return;
    }

    fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: body,
      keepalive: true,
    }).catch(function () {
      // Re-queue best-effort so events aren't lost on transient failures
      queue = batch.concat(queue);
    });
  }

  function track(name, properties) {
    queue.push({
      event_id:       uuid(),
      event_name:     name,
      event_category: null,
      properties:     properties || {},
      anonymous_id:   getAnonymousId(),
      session_id:     getSessionId(),
      page_url:       location.href,
      occurred_at:    new Date().toISOString(),
    });
    if (queue.length >= 50) flush(false); else scheduleFlush();
  }

  // Automatic page_view on initial load
  track('page_view', { path: location.pathname });

  // SPA route changes
  ['pushState', 'replaceState'].forEach(function (method) {
    var orig = history[method];
    history[method] = function () {
      orig.apply(this, arguments);
      track('page_view', { path: location.pathname });
    };
  });

  // Flush on unload / tab-hidden
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'hidden') flush(true);
  });
  window.addEventListener('pagehide', function () { flush(true); });

  window.Analytics = { track: track, flush: flush };
})(window, document);