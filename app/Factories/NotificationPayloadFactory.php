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
            'title' => [
                'en' => 'New Message',
                'ar' => 'رسالة جديدة',
            ],
            'body' => [
                'en' => 'Message from ' . $sender->username,
                'ar' => 'رسالة من ' . $sender->username,
            ],
            'data' => [
                'type' => 'chat',
                'chat_id' => (int) $chat->id,
                'product_id' => (int) $chat->product_id,
                'fromUser' => [
                    'id' => (int) $sender->id,
                    'name' => $sender->username,
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
            'title' => [
                'en' => 'New Offer!',
                'ar' => 'عرض جديد!',
            ],
            'body' => [
                'en' => "{$fromUser->username} offered {$price} on your {$productName}",
                'ar' => "{$fromUser->username} قدم عرضًا بقيمة {$price} على منتجك {$productName}",
            ],
            'data' => [
                'type' => 'offer',
                'offer_id' => (int) $offer->id,
                'chat_id' => (int) $offer->chat_id,
                'product_id' => (int) $offer->product->id ?? null,
                'fromUser' => [
                    'id' => (int) $fromUser->id,
                    'name' => $fromUser->username,
                    'profile_photo' => $fromUser->profile_photo,
                    'date' => now()->toDateTimeString(),
                ],
            ],
        ];
    }

    public static function productRejected(Product $product, array $reason): array
    {
        return [
            'title' => [
                'en' => 'Product Rejected',
                'ar' => 'تم رفض المنتج',
            ],
            'body' => [
            'en' => (($product->getTranslation('name', 'en')
                    ?? $product->getTranslation('name', 'ar'))
                . " was rejected. Reason: {$reason['en']}."),

            'ar' => "تم رفض المنتج '" .
                ($product->getTranslation('name', 'ar')
                    ?? $product->getTranslation('name', 'en'))
                . "'. السبب: {$reason['ar']}.",
            ],
            'data' => [
                'type' => 'product_rejected',
                'product_id' => (int) $product->id,
                'status' => $product->status,
                'date' => now()->toDateTimeString(),
            ],
        ];
    }
}
