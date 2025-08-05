<?php

namespace App\Services;

use App\Factories\NotificationPayloadFactory;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Repositories\OfferRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OfferService
{
    public function __construct(private readonly OfferRepository $offerRepository, private ChatService $chatService, private FirebaseNotificationService $firebaseNotificationService)
    {
    }

    public function createOffer($data, $user, $product)
    {
        if ($product->seller->id === $user->id) {
            throw new HttpException(403, __('messages.cannot_offer_own_product'));
        }

        $chat = $this->chatService->getOrCreateChat($product, $user->id);

        $data['product_id'] = $product->id;
        $data['user_id'] = $user->id;
        $data['chat_id'] = $chat->id;

        $offer = $this->offerRepository->create($data)->load(['product', 'user']);

        $offerData = [
            'content' => $offer->text,
            'type' => ChatMessage::$TYPE_TEXT,
        ];

        $this->chatService->sendMessage($chat, $user, $offerData);

        $notification = NotificationPayloadFactory::offer($offer);

        $this->firebaseNotificationService->sendNotification(
            $product->seller,
            $notification
        );

        $chat = Chat::getChatsWithProductSummary($user->id)->where('id', $chat->id)->first();

        return ['offer' => $offer , 'chat' => $chat];
    }
}
