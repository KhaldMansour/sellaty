// --- Load Axios ---
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// --- Get Token ---
const token = localStorage.getItem('access_token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    console.log('Token set in Axios:', token);
    console.log(process.env.VITE_REVERB_APP_KEY);
} else {
    console.warn('Token not found in localStorage.');
}

// --- Echo + Reverb Setup ---
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;


window.Echo = new Echo({
    broadcaster: 'reverb', // or 'pusher' depending on your setup
    key: process.env.VITE_REVERB_APP_KEY, // Use process.env to access environment variables
    wsHost: process.env.VITE_REVERB_HOST,
    wsPort: process.env.VITE_REVERB_PORT, // Access the WebSocket port
    wssPort: process.env.VITE_REVERB_WSS_PORT, // Access the secure WebSocket port
    forceTLS: process.env.VITE_REVERB_TLS === 'true', // Convert string to boolean for forceTLS
    enabledTransports: ['ws', 'wss'],
    auth: {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    },
    path: '/broadcasting', // Custom path to avoid '/app'
});



// --- Listen to Chat Events ---
window.Echo.private(`chat.1`)
    .listen('.ChatMessageSent', (e) => {
        console.log('Received message:', e);
        const msg = e.text;
        const el = document.getElementById('messages');
        el.innerHTML += `<p><strong>${e.sender_name}:</strong> ${msg}</p>`;
    });
