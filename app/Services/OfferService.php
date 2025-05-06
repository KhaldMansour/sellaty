<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ChatRepository;
use App\Repositories\OfferRepository;
use App\Repositories\RecentSearchRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OfferService
{
    public function __construct(private readonly OfferRepository $offerRepository, private ChatService $chatService)
    {
    }

    public function createOffer($data , $user , $product)
    {
        if ($product->seller->id === $user->id) {
            throw new HttpException(403, 'You cannot make an offer to a product you own.');
        }
        $data['product_id'] = $product->id;
        $data['user_id'] = $user->id;

        $offer = $this->offerRepository->create($data)->load(['product', 'user']);

        $chat = $this->chatService->getOrCreateChat($product , $user->id);

        $this->chatService->sendMessage($chat, $user->id, $offer->text);

        return ['offer' => $offer , 'chat' => $chat];
    }

}
