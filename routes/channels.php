<?php

use App\Models\Chat;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::with(['buyer', 'seller'])->find($chatId);

    return $chat && in_array($user->id, $chat->users->pluck('id')->toArray());
}, ['middleware' => [JwtMiddleware::class]]);
