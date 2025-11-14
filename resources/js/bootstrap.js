import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfToken =
    document.querySelector('meta[name="csrf-token"]')?.content ??
    (window.Laravel !== undefined ? window.Laravel.csrfToken : null);

if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

window.Pusher = Pusher;

const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';
const defaultHost = window.location.hostname === '' ? '127.0.0.1' : window.location.hostname;
const socketHost = import.meta.env.VITE_REVERB_HOST ?? defaultHost;
const socketPort = Number(import.meta.env.VITE_REVERB_PORT ?? (scheme === 'https' ? 443 : 8080));

// Get app credentials - these MUST match REVERB_APP_KEY and REVERB_APP_ID in .env
const appId = import.meta.env.VITE_REVERB_APP_ID ?? 'carline';
const appKey = import.meta.env.VITE_REVERB_APP_KEY ?? 'local';

console.log('Echo configuration:', {
    app_id: appId,
    key: appKey,
    wsHost: socketHost,
    wsPort: socketPort,
    scheme,
});

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: appKey,
    wsHost: socketHost,
    wsPort: socketPort,
    wssPort: socketPort,
    forceTLS: scheme === 'https',
    encrypted: scheme === 'https',
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    cluster: 'mt1', // Placeholder value required by pusher-js (ignored when using wsHost/wsPort)
    authEndpoint: '/broadcasting/auth',
});

// Debug Echo connection - wait for Pusher to be ready
setTimeout(() => {
    try {
        const pusher = window.Echo.connector?.pusher;
        if (!pusher) {
            console.error('Pusher connector not found');
            return;
        }

        const connection = pusher.connection;
        if (!connection) {
            console.error('Pusher connection not found');
            return;
        }

        console.log('Current connection state:', connection.state);

        connection.bind('state_change', (states) => {
            console.log('Echo connection state:', states.current);
            if (states.current === 'connected') {
                console.log('✅ Echo connected successfully!');
            }
        });

        connection.bind('connected', () => {
            console.log('✅ Echo connected successfully!');
        });

        connection.bind('error', (err) => {
            console.error('❌ Echo connection error:', err);
        });

        connection.bind('disconnected', () => {
            console.log('🔌 Echo disconnected');
        });
    } catch (error) {
        console.error('Error setting up Echo debug listeners:', error);
    }
}, 100);
