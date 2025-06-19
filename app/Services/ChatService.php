<?php

namespace App\Services;

use App\Repositories\ChatRepository;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Events\ChatMessageSent;
use App\Events\MessagesSeen;
use App\Factories\NotificationPayloadFactory;
use App\Models\User;

class ChatService
{
    public function __construct(private readonly ChatRepository $chatRepository, private readonly FirebaseNotificationService $firebaseNotificationService)
    {
    }

    public function getOrCreateChat($product, $userId): Chat
    {
        $sellerId = $product->seller->id;

        $chat = Chat::firstOrCreate(
            ['product_id' => $product->id, 'buyer_id' => $userId],
            ['seller_id' => $sellerId]
        );

        return Chat::getChatsWithProductSummary($userId)->where('id', $chat->id)->first();
    }

    public function getChat(int $chatId, int $userId): Chat
    {
        $chat = Chat::with(['buyer', 'seller'])->find($chatId);

        if (!$chat || !in_array($userId, $chat->users->pluck('id')->toArray())) {
            abort(403, 'You are not authorized to access this chat.');
        }

        return Chat::getChatsWithProductSummary($userId)->where('id', $chat->id)->first();
    }

    public function sendMessage(Chat $chat, User $user, array $data): ChatMessage
    {
        $content = $data['content'] ?? null;

        if (in_array($data['type'], ['image', 'voice']) && isset($data['file'])) {
            $file = $data['file'];
            $content = $file->store('chat_uploads');
        }
        $receiver = $chat->buyer_id === $user->id
            ? $chat->seller
            : $chat->buyer;

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'type' => $data['type'],
            'content' => $content,
        ]);

        broadcast(new ChatMessageSent($message));

        $notification = NotificationPayloadFactory::chat($message);

        $this->firebaseNotificationService->sendNotification(
            $receiver->fcm_token,
            $notification
        );

        return $message;
    }

    // public function sendMessageTest(string $text)
    // {
    //     // $message = ChatMessage::create([
    //     //     'chat_id' => $chat->id,
    //     //     'sender_id' => $senderId,
    //     //     'text' => $text,
    //     // ]);

    //     broadcast(new ChatMessageSent($text));

    //     return $text;
    // }

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
