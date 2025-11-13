import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;

const protocol = import.meta.env.VITE_WEBSOCKETS_SCHEME ?? 'http';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_WEBSOCKETS_KEY ?? 'local',
    wsHost: import.meta.env.VITE_WEBSOCKETS_HOST ?? window.location.hostname,
    wsPort: Number(import.meta.env.VITE_WEBSOCKETS_PORT ?? 6001),
    wssPort: Number(import.meta.env.VITE_WEBSOCKETS_PORT ?? 6001),
    forceTLS: protocol === 'https',
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    encrypted: protocol === 'https',
});
