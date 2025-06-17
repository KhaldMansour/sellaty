<?php

namespace App\Factories;

use App\Models\ChatMessage;
use App\Models\Offer;

class NotificationPayloadFactory
{
    public static function chat(ChatMessage $chatMessage): array
    {
        $sender = $chatMessage->sender;
        $chat = $chatMessage->chat;

        return [
            'title' => 'New Message',
            'body' => 'Message from ' . $sender->first_name,
            'data' => [
                'type' => 'chat',
                'chat_id' => (int) $chat->id,
                'product_id' => (int) $chat->product_id,
                'fromUser' => [
                    'id' => (int) $sender->id,
                    'name' => $sender->full_name,
                    'profile_photo' => $sender->profile_photo,
                    'date' => now()->toDateTimeString(),
                ],
            ],
        ];
    }

    public static function offer(Offer $offer): array
    {
        $fromUser = $offer->user;

        return [
            'title' => 'Special Offer!',
            'body' => 'You have a new offer waiting',
            'data' => [
                'type' => 'offer'
            ],
        ];
    }
}
