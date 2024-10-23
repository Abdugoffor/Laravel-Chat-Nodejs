<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Foydalanuvchilar ro'yxatini va chatlarni ko'rsatish
    public function index()
    {
        $users = User::where('id', '!=', Auth::id())->get(); // O'zingizdan boshqa barcha foydalanuvchilarni oling
        return view('chat.index', compact('users'));
    }

    // Yangi chat yaratish yoki mavjud chatni olish
    public function startChat($userId)
    {
        $authId = Auth::id();

        $chat = Chat::where(function ($query) use ($authId, $userId) {
            $query->where('from_id', $authId)
                ->where('to_id', $userId);
        })->orWhere(function ($query) use ($authId, $userId) {
            $query->where('from_id', $userId)
                ->where('to_id', $authId);
        })->first();

        if (!$chat) {
            $chat = Chat::create([
                'from_id' => $authId,
                'to_id' => $userId,
            ]);
        }

        $users = User::where('id', '!=', Auth::id())->get();

        $messages = $chat->messages();

        return view('chat.show', compact('chat', 'users', 'messages'));
    }

    public function sendMessage(Request $request, $chatId)
    {
        $chat = Chat::findOrFail($chatId);

        $message = $chat->messages()->create([
            'text' => $request->text,
            'sender_id' => Auth::id(),
        ]);
        // Node.js serveriga HTTP so'rov yuborish
        $client = new Client();
        
        $client->post('http://localhost:3000/send-message', [
            'json' => [
                'name' => Auth::user()->name,
                'chat_id' => $chatId, // Chatning ID si yuborilyapti
                'sender_id' => Auth::id(), // Yuboruvchi ID si
                'text' => $request->text, // Xabar matni
            ],
        ]);

        return redirect()->back();
    }

    public function show($chatId)
    {
        $chat = Chat::findOrFail($chatId);
        $messages = $chat->messages;
        $users = User::where('id', '!=', Auth::id())->get(); // Chatdan tashqari foydalanuvchilar ro'yxati

        return view('chat.index', compact('chat', 'messages', 'users'));
    }
}
