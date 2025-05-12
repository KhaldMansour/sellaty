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
    }
});



window.Echo.connector.pusher.connection.bind('connected', () => {
    let socketId = window.Echo.connector.pusher.connection.socket_id;
    axios.defaults.headers.common['X-Socket-ID'] = socketId;
    console.log('Socket ID connected:', socketId);
    console.log('host:', process.env.VITE_REVERB_HOST);
    console.log('key:', process.env.VITE_REVERB_APP_KEY);
    console.log('key:', process.env.VITE_REVERB_PORT);



    console.log('secret:', process.env.REVERB_APP_SECRET);
    console.log('q connection:', process.env.QUEUE_CONNECTION);
    console.log('b connection:', process.env.BROADCAST_CONNECTION);

});

// Send Message Function
window.sendMessage = function () {
    const type = document.getElementById('type').value;
    const text = document.getElementById('text').value;
    const file = document.getElementById('file').files[0];

    const formData = new FormData();
    formData.append('type', type);

    if (type === 'text') {
        formData.append('content', text);
    } else if (file) {
        formData.append('file', file);
    } else {
        alert('Please select a file.');
        return;
    }

    axios.post('/api/v1/chats/1/messages', formData)
        .then(() => {
            document.getElementById('text').value = '';
            document.getElementById('file').value = '';
        })
        .catch(error => {
            console.error("Send failed:", error);
            // alert("Failed to send message.");
        });
};


// --- Listen to Chat Events ---
window.Echo.private(`chat.1`)
    .listen('.ChatMessageSent', (e) => {
        console.log('Received message:', e);
        const msg = e.content;
        const el = document.getElementById('messages');
        if (e.type !== 'text') { 
           el.innerHTML += `<img src="${e.content}" width="200" />`;
        }else{
            el.innerHTML += `<p><strong>${e.sender_name}:</strong> ${msg}</p>`;
        }
    });
