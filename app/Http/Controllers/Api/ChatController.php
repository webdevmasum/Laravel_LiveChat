<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(): JsonResponse
    {
        // Fetch users with the last message for each user
        $users = User::with([
            'senders' => function ($query) {
                $query->where('receiver_id', Auth::id())
                    ->latest()
                    ->limit(1); // Fetch only the latest message
            },
            'receivers' => function ($query) {
                $query->where('sender_id', Auth::id())
                    ->latest()
                    ->limit(1); // Fetch only the latest message
            },
        ])->where('id', '!=', Auth::id())->get();

        // Map users with their last message
        $usersWithMessages = $users->map(function ($user) {
            // Check for the last message from either sender or receiver
            $lastMessage = $user->senders->first() ?: $user->receivers->first();

            return [
                'user' => $user,
                'last_message' => $lastMessage,
            ];
        })->filter(function ($item) {
            return $item['last_message'] !== null;
        });

        // Sort users by the last message's created_at timestamp in descending order
        $sortedUsersWithMessages = $usersWithMessages->sortByDesc(function ($item) {
            return $item['last_message']->created_at;
        })->values();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Users retrieved successfully',
            'data' => $sortedUsersWithMessages,
        ], 200);
    }




    //! this function will send message working perfectly
      public function sendMessage(Request $request)
    {
        $message = ChatMessage::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'text' => $request->message,

            //! in database conversation_id will no need if it's not use
            // 'conversation_id' => $request->conversation_id

        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data'    => $message,
        ], 200);
    }
}
