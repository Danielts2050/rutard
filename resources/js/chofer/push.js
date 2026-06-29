export function initPush(vapidPublicKey) {
    if (!('Notification' in window) || !('serviceWorker' in navigator)) return;
    if (!vapidPublicKey) return;

    if (Notification.permission === 'granted') {
        subscribe(vapidPublicKey);
    } else if (Notification.permission === 'default') {
        Notification.requestPermission().then(perm => {
            if (perm === 'granted') subscribe(vapidPublicKey);
        });
    }
}

async function subscribe(vapidPublicKey) {
    try {
        const reg = await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
        });

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        if (!csrf) return;

        await fetch('/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({
                endpoint: sub.endpoint,
                p256dh: btoa(String.fromCharCode(...new Uint8Array(sub.getKey('p256dh')))),
                auth: btoa(String.fromCharCode(...new Uint8Array(sub.getKey('auth')))),
            }),
        });
    } catch (e) {
        console.warn('Push subscription failed:', e);
    }
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);
    return Uint8Array.from([...rawData].map(ch => ch.charCodeAt(0)));
}
