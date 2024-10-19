<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;

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
        $chat = Chat::firstOrCreate([
            'from_id' => Auth::id(),
            'to_id' => $userId
        ]);

        // Foydalanuvchilar ro'yxatini olish
        $users = User::where('id', '!=', Auth::id())->get();

        // Faqat ushbu chatga tegishli xabarlarni olish
        $messages = $chat->messages()->where(function ($query) use ($userId) {
            $query->where('sender_id', Auth::id())
                ->orWhere('sender_id', $userId);
        })->get();

        return view('chat.show', compact('chat', 'users', 'messages'));
    }


    // Xabar yuborish
    public function sendMessage(Request $request, $chatId)
    {
        $chat = Chat::findOrFail($chatId);

        // Xabarni bazaga saqlash
        $message = $chat->messages()->create([
            'text' => $request->text,
            'sender_id' => Auth::id(),
        ]);

        // Node.js serveriga HTTP so'rov yuborish
        $client = new Client();
        // dd($client, $chatId, $request->text, Auth::id());
        $client->post('http://localhost:3000/send-message', [
            'json' => [
                'chat_id' => $chatId,
                'sender_id' => Auth::id(),
                'text' => $request->text,
            ]
        ]);

        return redirect()->back();
    }

    // Chatdagi xabarlarni ko'rsatish
    public function show($chatId)
    {
        $chat = Chat::findOrFail($chatId);
        $messages = $chat->messages;
        $users = User::where('id', '!=', Auth::id())->get(); // Chatdan tashqari foydalanuvchilar ro'yxati

        return view('chat.index', compact('chat', 'messages', 'users'));
    }
}
