<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $chatMessage;

    public function __construct(ChatMessage $chatMessage)
    {
        $this->chatMessage = $chatMessage;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->chatMessage->chat_id);
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->chatMessage->id,
            'text' => $this->chatMessage->text,
            'sender_id' => $this->chatMessage->sender_id,
            'sender_name' => $this->chatMessage->sender->name,
            'chat_id' => $this->chatMessage->chat_id,
            'seen_at' => $this->chatMessage->seen_at,
            'created_at' => $this->chatMessage->created_at->toISOString(),
        ];
    }

    public function broadcastAs()
    {
        return 'ChatMessageSent';
    }
}
