<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-secondary">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <h5>Users {{ Auth::user()->name }}</h5>
                <ul class="list-group">
                    @foreach ($users as $user)
                        <li class="list-group-item">
                            <a href="{{ route('chat.start', $user->id) }}">{{ $user->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="col-md-8">
                @if (isset($chat))
                    @php
                        // dd($chat->toUser->name);
                    @endphp
                    <h5>Chat with {{ $chat->toUser->name }}</h5>
                    <div id="chatBox" class="border p-3 mb-3" style="height: 300px; overflow-y: scroll;">
                        @foreach ($messages as $message)
                            <p>
                                <strong>{{ $message->sender_id == auth()->id() ? 'You' : $message->sender->name }}:</strong>
                                {{ $message->text }}
                            </p>
                        @endforeach
                    </div>

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

    <script src="https://cdn.socket.io/4.3.2/socket.io.min.js"></script>

    @if (isset($chat))
        <script>
            var socket = io('http://localhost:3000');

            // Foydalanuvchini chat kanaliga ulash
            socket.emit('joinChat', {{ $chat->id }}); // Foydalanuvchini chatga ulash

            // Yangi xabar kelganda chat oynasiga qo'shish
            socket.on('newMessage', function(message) {
                if (message.chat_id == {{ $chat->id }}) { // Faqat hozirgi chatga tegishli xabarlar
                    var currentUser = {{ auth()->id() }};
                    var sender = (message.sender_id == currentUser) ? 'You' : message.name;

                    var chatBox = document.getElementById('chatBox');
                    chatBox.innerHTML += `<p><strong>${sender}:</strong> ${message.text}</p>`;
                    chatBox.scrollTop = chatBox.scrollHeight; // Scrollni oxiriga olib borish
                }
            });


            // Oldingi xabarlarni olish
            socket.on('previousMessages', function(messages) {
                var chatBox = document.getElementById('chatBox');
                chatBox.innerHTML = ''; // Chat oynasini tozalaymiz

                messages.forEach(function(message) {
                    if (message.chat_id == {{ $chat->id }}) { // Faqat hozirgi chatga tegishli xabarlar
                        var sender = (message.sender_id == {{ auth()->id() }}) ? 'You' : message.name;
                        chatBox.innerHTML += `<p><strong>${sender}:</strong> ${message.text}</p>`;
                    }
                });
                chatBox.scrollTop = chatBox.scrollHeight; // Scrollni oxiriga olib borish
            });
        </script>
    @endif


</body>

</html>
