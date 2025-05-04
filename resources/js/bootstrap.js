import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = localStorage.getItem('access_token');

if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    console.log('Token set in Axios:', token);
} else {
    console.warn('Token not found in localStorage.');
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
