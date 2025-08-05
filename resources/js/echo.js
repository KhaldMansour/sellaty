import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;
const token = localStorage.getItem('access_token');


window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    auth: {
        headers: {
            Authorization: `Bearer ${token}`
        }
    }
});


window.Echo.private(`chat.1`)
.listen('.ChatMessageSent', (e) => {
    console.log(e);
    
    const msg = e.text;
    const el = document.getElementById('messages');
    el.innerHTML += `<p><strong>${e.sender_name}:</strong> ${msg}</p>`;
});

