<?php

namespace App\Http\Controllers\Web\Backend\Chat;

use App\Events\MessageSend;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    
    /**     
     * - show: show the chat view
     * - getMessages: get the messages for the given user
     * - sendMessage: send a message to the given user
     */
    public function show(User $user)
    {
        return view('chat', compact('user'));
    }


    
    /**
     * get the messages for the given user
     */
    public function getMessages(User $user)
    {
        return Message::query()
            ->where(function ($query) use ($user) {
                $query->where('sender_id', auth()->id())
                    ->where('receiver_id', $user->id);                
            })
            ->orWhere(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', auth()->id());
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    
    /**
     * send a message to the given user
     */
    public function sendMessage(User $user)
    {
        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
            'text' => request('message'),
        ]);

        broadcast(new MessageSend($message));

        return response()->json($message);

    }


}
