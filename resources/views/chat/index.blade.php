<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">
        <div class="row">
            <!-- Chap tomon: Foydalanuvchilar ro'yxati -->
            <div class="col-md-4">
                <h5>Users</h5>
                <ul class="list-group">
                    @foreach ($users as $user)
                        <li class="list-group-item">
                            <a href="{{ route('chat.start', $user->id) }}">
                                {{ $user->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- O'ng tomon: Xabarlar oynasi va yuborish formasi -->
            <div class="col-md-8">
                @if (isset($chat))
                    <h5>Chat with {{ $chat->toUser->name }}</h5>
                    <div id="chatBox" class="border p-3 mb-3" style="height: 300px; overflow-y: scroll;">
                        @foreach ($messages as $message)
                            <p><strong>{{ $message->sender_id == auth()->id() ? 'You' : $message->sender->name }}:</strong>
                                {{ $message->text }}</p>
                        @endforeach
                    </div>

                    <!-- Xabar yuborish formasi -->
                    <form action="{{ route('chat.send', $chat->id) }}" method="POST" id="chatForm">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="text" id="messageInput" class="form-control"
                                placeholder="Type a message..." required>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                    </form>
                @else
                    <h5>Select a user to start chatting</h5>
                @endif
            </div>
        </div>
    </div>

    <!-- Socket.IO kutubxonasi -->
    <script src="https://cdn.socket.io/4.3.2/socket.io.min.js"></script>

    @if (isset($chat))
        <script>
            // Socket.io orqali ulanish
            var socket = io('http://localhost:3000');

            // Yangi xabar kelganda chat oynasiga qo'shish
            socket.on('newMessage', function(message) {
                var currentUser = {{ auth()->id() }};
                var sender = (message.sender_id == currentUser) ? 'You' : 'User ' + message.sender_id;

                var chatBox = document.getElementById('chatBox');
                chatBox.innerHTML += `<p><strong>${sender}:</strong> ${message.text}</p>`;
                chatBox.scrollTop = chatBox.scrollHeight; // Scrollni oxiriga olib borish
            });

            // Oldingi xabarlarni olish
            socket.on('previousMessages', function(messages) {
                var chatBox = document.getElementById('chatBox');
                messages.forEach(function(message) {
                    var sender = (message.sender_id == {{ auth()->id() }}) ? 'You' : 'User ' + message
                        .sender_id;
                    chatBox.innerHTML += `<p><strong>${sender}:</strong> ${message.text}</p>`;
                });
                chatBox.scrollTop = chatBox.scrollHeight; // Scrollni oxiriga olib borish
            });
        </script>
    @endif

</body>

</html>
