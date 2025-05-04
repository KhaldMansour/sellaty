<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Services\ChatService;

class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chatService)
    {
    }
    // public function index()
    // {
    //     $chat = Chat::find(1);

    //     return view('test-chat', ['chat' => $chat]);
    // }

    public function getOrCreate($productId)
    {
        $buyerId = auth()->id();
        $chat = $this->chatService->getOrCreateChat($productId, $buyerId);

        return $this->success($chat);
    }

    public function buyerChats()
    {
        $userId = auth()->id();
        $buyerChats = $this->chatService->getBuyerChatsWithUnseenCount($userId);

        return $this->success($buyerChats);
    }

    public function sellerChats()
    {
        $userId = auth()->id();
        $sellerChats = $this->chatService->getSellerChatsWithUnseenCount($userId);

        return $this->success($sellerChats);
    }
}
