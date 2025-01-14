<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('presense.{id}', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});



//! live chat channel for API
Broadcast::channel('chat.{receiver_id}', function ($user, $receiver_id) {
    return (int) $user->id === (int) $receiver_id;
});



