<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chat Interface</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h3>Chat Interface (Chat ID: 1)</h3>

    <div id="messages" style="height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;"></div>

    <br>

    <select id="type">
        <option value="text">Text</option>
        <option value="image">Image</option>
        <option value="voice">Voice</option>
    </select>

    <input type="text" id="text" placeholder="Enter message...">
    <input type="file" id="file">
    <button onclick="sendMessage()">Send</button>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
