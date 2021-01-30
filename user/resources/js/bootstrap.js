/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo'

window.io = require('socket.io-client')

// let token = document.head.querySelector('meta[name="csrf-token"]');
window.Echo = new Echo({
    broadcaster: 'socket.io',
    namespace: 'App.Events',
    host: window.location.hostname + ':6001',
    // auth: {
    //     headers: {
    //         'X-XSRF-TOKEN' : token
    //     }
    // }
});
