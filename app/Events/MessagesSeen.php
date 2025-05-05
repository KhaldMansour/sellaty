<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessagesSeen implements ShouldBroadcast
{
    use SerializesModels;

    public $chatId;
    public $userId;

    public function __construct($chatId, $userId)
    {
        $this->chatId = $chatId;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return new Channel("chat.$this->chatId");
    }

    public function broadcastAs()
    {
        return 'MessagesSeen';
    }
}
