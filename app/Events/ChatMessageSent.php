<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Faker\Factory as Faker;


class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $chatMessage;

    public function __construct($chatMessage)
    {
        $this->chatMessage = $chatMessage;
    }

    public function broadcastOn()
    {
        return new Channel('chat');
    }

    public function broadcastWith()
    {
        $faker = Faker::create();

        return [
            'id' => $faker->randomNumber(),
            'text' => $this->chatMessage,
            'sender_id' => $faker->randomNumber(),
            'sender_name' => $faker->name,
            'chat_id' => $faker->randomNumber(),
            'seen_at' => now()->toISOString(),
            'created_at' => $faker->dateTimeThisMonth->format(DATE_ISO8601),
        ];
    }

    public function broadcastAs()
    {
        return 'ChatMessageSent';
    }
}
