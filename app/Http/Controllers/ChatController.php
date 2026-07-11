<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ChatController extends Controller
{
    public function getMessages(): JsonResponse
    {
        $messages = Message::query()
            ->with('sender:id,name,email')
            ->oldest()
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    public function storeMessage(Request $request)
    {
        $validated = $request->validate([
            'sender_id' => ['required', 'exists:users,id'],
            'content' => ['required', 'string'],
        ]);

        $message = Message::create([
            'sender_id' => $validated['sender_id'],
            'content' => $validated['content'],
        ]);

        $message->load('sender:id,name,email');

        $payload = [
            'id' => $message->id,
            'content' => $message->content,
            'sender' => [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'email' => $message->sender->email,
            ],
            'created_at' => $message->created_at->toISOString(),
        ];

        $subscribers = Redis::publish(
            'chat-message-created',
            json_encode($payload)
        );

        return response()->json([
            'success' => true,
            'redis_subscribers' => $subscribers,
            'message' => $payload,
        ], 201);
    }
}
