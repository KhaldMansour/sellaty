<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendChatMessageRequest;
use App\Models\Chat;
use App\Services\ChatMessageService;
use App\Services\ChatService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ChatMessageController extends Controller
{
    public function __construct(private readonly ChatService $chatService, private readonly ChatMessageService $chatMessageService)
    {
    }
    public function send(SendChatMessageRequest $request, Chat $chat)
    {
        $message = $this->chatService->sendMessage($chat, auth()->id(), $request->text);

        return $this->success($message, 'Message sent successfully', 201);
    }

    public function messages(Chat $chat)
    {
        $userId = auth()->id();

        if ($chat->buyer_id !== $userId && $chat->seller_id !== $userId) {
            throw new HttpException(403, 'You do not have access to this chat.');
        }

        $messages = $this->chatMessageService->getPaginatedMessages($chat);

        return $this->success($messages, 'Messages retrieved successfully');
    }

    // public function markAsSeen(Chat $chat)
    // {
    //     $this->chatService->markMessagesAsSeen($chat, auth()->id());

    //     return response()->json(['status' => 'seen']);
    // }
}
