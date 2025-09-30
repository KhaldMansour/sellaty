<?php

namespace App\Factories;

use App\Models\ChatMessage;
use App\Models\Offer;
use App\Models\Product;

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
        $productName = $offer->product->name;
        $price = $offer->price;

        return [
            'title' => 'New Offer!',
            'body' => "{$fromUser->full_name} offered {$price} on your {$productName}",
            'data' => [
                'type' => 'offer',
                'offer_id' => (int) $offer->id,
                'chat_id' => (int) $offer->chat_id,
                'product_id' => (int) $offer->product->id ?? null,
                'fromUser' => [
                    'id' => (int) $fromUser->id,
                    'name' => $fromUser->full_name,
                    'profile_photo' => $fromUser->profile_photo,
                    'date' => now()->toDateTimeString(),
                ],
            ],
        ];
    }

    public static function productRejected(Product $product, string $reason): array
    {
        return [
            'title' => 'Product Rejected',
            'body' => "Your product '{$product->name}' was rejected. Reason: {$reason}.",
            'data' => [
                'type' => 'product_rejected',
                'product_id' => (int) $product->id,
                'status' => $product->status,
                'reason' => $reason,
                'date' => now()->toDateTimeString(),
            ],
        ];
    }
}
