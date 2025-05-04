<?php

namespace App\Services;

use App\Repositories\ChatRepository;
use App\Models\Chat;

class ChatMessageService
{
    public function __construct(private readonly ChatRepository $chatRepository)
    {
    }

    public function getPaginatedMessages(Chat $chat, int $perPage = 15)
    {
        return $chat->messages()
            ->with('sender')
            ->latest()
            ->paginate($perPage);
    }
}
