import { initPush } from './push';

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js');
    });
}

const metaKey = document.querySelector('meta[name="vapid-public-key"]');
const vapidKey = metaKey?.content || '';
initPush(vapidKey);
