<?php

namespace App\Services;

use App\Repositories\ChatRepository;
use App\Models\Chat;
use App\Models\Product;
use App\Models\ChatMessage;
use App\Events\ChatMessageSent;
use App\Events\MessagesSeen;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ChatService
{
    public function __construct(private readonly ChatRepository $chatRepository)
    {
    }

    public function getOrCreateChat($product, $buyerId): Chat
    {
        $sellerId = $product->seller->id;

        if ($buyerId === $sellerId) {
            throw new HttpException(403, 'You cannot start a chat with yourself.');
        }

        return Chat::firstOrCreate(
            ['product_id' => $product->id, 'buyer_id' => $buyerId],
            ['seller_id' => $sellerId]
        );
    }

    public function sendMessage(Chat $chat, int $senderId, string $text): ChatMessage
    {
        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => $senderId,
            'text' => $text,
        ]);

        broadcast(new ChatMessageSent($message));

        return $message;
    }

    public function getBuyerChatsWithUnseenCount(int $userId)
    {
        return Chat::where('buyer_id', $userId)
        ->with(['product', 'buyer', 'seller'])
        ->withCount([
                'messages as unseen_messages_count' => function ($query) use ($userId) {
                    $query->where('sender_id', '!=', $userId)
                          ->whereNull('seen_at');
                }
            ])
            ->get();
    }

    public function getSellerChatsWithUnseenCount(int $userId)
    {
        return Chat::where('seller_id', $userId)
        ->with(['product', 'buyer', 'seller'])
        ->withCount([
                'messages as unseen_messages_count' => function ($query) use ($userId) {
                    $query->where('sender_id', '!=', $userId)
                          ->whereNull('seen_at');
                }
            ])
            ->get();
    }

    // public function markMessagesAsSeen(Chat $chat, int $userId): void
    // {
    //     $chat->messages()
    //         ->where('sender_id', '!=', $userId)
    //         ->whereNull('seen_at')
    //         ->update(['seen_at' => now()]);

    //     broadcast(new MessagesSeen($chat->id, $userId))->toOthers();
    // }
}
