<?php

namespace App\Services;

use App\Events\MessagesSeen;
use App\Repositories\ChatRepository;
use App\Models\Chat;
use App\Models\User;

class ChatMessageService
{
    public function __construct(private readonly ChatRepository $chatRepository)
    {
    }

    public function getPaginatedMessages(Chat $chat, int $perPage = 100)
    {
        return $chat->messages()
            ->with('sender')
            ->latest()
            ->paginate($perPage);
    }

    public function markMessagesAsSeen(User $user, Chat $chat)
    {
        $chat->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        broadcast(new MessagesSeen($chat->id, $user->id))->toOthers();

        return;
    }
}
