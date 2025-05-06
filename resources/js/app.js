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
    authEndpoint: '/broadcasting/auth',
});



window.Echo.connector.pusher.connection.bind('connected', () => {
    let socketId = window.Echo.connector.pusher.connection.socket_id;
    axios.defaults.headers.common['X-Socket-ID'] = socketId;
    console.log('Socket ID connected:', socketId);
    console.log('host:', process.env.VITE_REVERB_HOST);
    console.log('secret:', process.env.VITE_REVERB_APP_SECRET);
    console.log('q connection:', process.env.QUEUE_CONNECTION);
    console.log('b connection:', process.env.BROADCAST_CONNECTION);

});

// --- Listen to Chat Events ---
window.Echo.private(`chat.1`)
    .listen('.ChatMessageSent', (e) => {
        console.log('Received message:', e);
        const msg = e.text;
        const el = document.getElementById('messages');
        el.innerHTML += `<p><strong>${e.sender_name}:</strong> ${msg}</p>`;
    });
