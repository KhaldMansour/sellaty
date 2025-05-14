<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Repositories\OfferRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OfferService
{
    public function __construct(private readonly OfferRepository $offerRepository, private ChatService $chatService)
    {
    }

    public function createOffer($data, $user, $product)
    {
        if ($product->seller->id === $user->id) {
            throw new HttpException(403, 'You cannot make an offer to a product you own.');
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

        return ['offer' => $offer , 'chat' => $chat];
    }
}
