<?php

namespace App\Services;

use App\Repositories\ChatRepository;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Events\ChatMessageSent;
use App\Events\MessagesSeen;
use App\Models\User;
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

        $chat = Chat::firstOrCreate(
            ['product_id' => $product->id, 'buyer_id' => $buyerId],
            ['seller_id' => $sellerId]
        );

        return Chat::getChatsWithProductSummary($buyerId)->where('id', $chat->id)->first();
    }

    public function sendMessage(Chat $chat, User $user, array $data): ChatMessage
    {
        $content = $data['content'] ?? null;

        if (in_array($data['type'], ['image', 'voice']) && isset($data['file'])) {
            $file = $data['file'];
            $content = $file->store('chat_uploads');
        }

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'type' => $data['type'],
            'content' => $content,
        ]);

        broadcast(new ChatMessageSent($message));

        return $message;
    }

    public function sendMessageTest(string $text)
    {
        // $message = ChatMessage::create([
        //     'chat_id' => $chat->id,
        //     'sender_id' => $senderId,
        //     'text' => $text,
        // ]);

        broadcast(new ChatMessageSent($text));

        return $text;
    }

    public function getBuyerChatsWithUnseenCount(int $userId)
    {
        return Chat::getChatsWithProductSummary($userId)
            ->where('buyer_id', $userId)
            ->get();
    }

    public function getSellerChatsWithUnseenCount(int $userId)
    {
        return Chat::getChatsWithProductSummary($userId)
            ->where('seller_id', $userId)
            ->get();
    }

    public function getChatsWithUnseenCount(int $userId)
    {
        return Chat::getChatsWithProductSummary($userId)
            ->where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
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
