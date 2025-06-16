<?php

namespace App\Factories;

use App\Models\ChatMessage;
use App\Models\Offer;

class NotificationPayloadFactory
{
    public static function chat(ChatMessage $chatMessage): array
    {
        $sender = $chatMessage->sender;

        return [
            'title' => 'New Message',
            'body' => 'Message from ' . $sender->first_name,
            'data' => [
                'type' => 'chat',
                'chat_id' => $chatMessage->chat->id,
                'sender' => [
                    'id' => $sender->id,
                    'name' => $sender->full_name,
                    'profile_photo' => $sender->profile_photo,
                ],
            ],
        ];
    }

    public static function offer(Offer $offer): array
    {
        return [
            'title' => 'Special Offer!',
            'body' => 'You have a new offer waiting',
            'data' => [
                'type' => 'offer',
                'offer_id' => $offer->id,
                'user' => [
                    'id' => $offer->user->id,
                    'name' => $offer->user->full_name,
                    'profile_photo' => $offer->user->profile_photo,
                ],
            ],
        ];
    }
}
