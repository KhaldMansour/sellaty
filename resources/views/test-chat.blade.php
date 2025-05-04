<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chat Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h3>Chat Test for Chat ID 1</h3>
    <div id="messages" style="height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;"></div>

    <input type="text" id="text" placeholder="Enter message...">
    <button onclick="sendMessage()">Send</button>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    @vite('resources/js/app.js')

    <script>
        const chatId = 1;
        const token = localStorage.getItem('access_token');

        // if (!token) {
        //     alert("Authorization token not found in local storage.");
        // }

        // Set up Axios with token
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

        // Connect to private channel
        // window.Echo.channel(`chat.`)
        //     .listen('ChatMessageSent', (e) => {
        //         console.log(e);
                
        //         const msg = e.chatMessage;
        //         const name = msg.sender.name ?? 'User';
        //         document.getElementById('messages').innerHTML += `<p><strong>${name}:</strong> ${msg.text}</p>`;
        //     });

        // const token = localStorage.getItem('access_token');
        if (token) {
            window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            console.log('Token set in Axios:', token);
        } else {
            console.warn('Token not found in localStorage.');
        }

        // Send message via API
        function sendMessage() {
            const text = document.getElementById('text').value;
            if (!text.trim()) return;
            

            axios.post(`/api/v1/chats/${chatId}/messages`, { text })
            .then(() => {
                document.getElementById('text').value = '';
            })
            .catch(error => {
                console.error("Message send error:", error);
                alert("Failed to send message.");
            });
        }
    </script>
</body>
</html>
