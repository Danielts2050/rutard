(function() {
    'use strict';

    var vapidKey = document.querySelector('meta[name="vapid-public-key"]');
    if (!vapidKey || !vapidKey.content) return;

    var applicationServerKey = urlBase64ToUint8Array(vapidKey.content);

    if (!('Notification' in window) || !('serviceWorker' in navigator)) return;

    if (Notification.permission === 'granted') {
        subscribe();
    } else if (Notification.permission === 'default') {
        Notification.requestPermission(function(perm) {
            if (perm === 'granted') subscribe();
        });
    }

    function subscribe() {
        navigator.serviceWorker.ready.then(function(reg) {
            if (reg.pushManager) {
                reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: applicationServerKey
                }).then(function(sub) {
                    var csrf = document.querySelector('meta[name="csrf-token"]');
                    if (!csrf) return;
                    fetch('/push/subscribe', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.content },
                        body: JSON.stringify({
                            endpoint: sub.endpoint,
                            p256dh: btoa(String.fromCharCode.apply(null, new Uint8Array(sub.getKey('p256dh')))),
                            auth: btoa(String.fromCharCode.apply(null, new Uint8Array(sub.getKey('auth'))))
                        })
                    }).catch(function() {});
                }).catch(function() {});
            }
        }).catch(function() {});
    }

    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var rawData = atob(base64);
        var output = new Uint8Array(rawData.length);
        for (var i = 0; i < rawData.length; i++) {
            output[i] = rawData.charCodeAt(i);
        }
        return output;
    }
})();
