<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendChatMessageRequest;
use App\Http\Resources\ChatMessageResource;
use App\Models\Chat;
use App\Services\ChatMessageService;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ChatMessageController extends Controller
{
    public function __construct(private readonly ChatService $chatService, private readonly ChatMessageService $chatMessageService)
    {
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chats/{chat}/messages",
     *     summary="Send a message in a chat",
     *     description="Send a new message from the authenticated user to a specific chat.",
     *     operationId="sendChatMessage",
     *     tags={"Chats"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="chat",
     *         in="path",
     *         required=true,
     *         description="Chat ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SendChatMessageRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Message sent successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ChatMessageResource")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="text",
     *                     type="array",
     *                     @OA\Items(type="string", example="The text field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function send(SendChatMessageRequest $request, Chat $chat)
    {
        $message = $this->chatService->sendMessageTest($request->text);

        return $this->success($message, 'Message sent successfully', 201);

        // $message = $this->chatService->sendMessage($chat, auth()->id(), $request->text);

        // return $this->success(new ChatMessageResource($message), 'Message sent successfully', 201);
    }

    public function sendTest(Request $request)
    {
        $message = $this->chatService->sendMessageTest($request->text);

        return $this->success($message, 'Message sent successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chats/{chat}/messages",
     *     summary="Get messages for a chat",
     *     description="Returns a paginated list of messages for the specified chat. Only participants (buyer or seller) can access it.",
     *     operationId="getChatMessages",
     *     tags={"Chats"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="chat",
     *         in="path",
     *         required=true,
     *         description="ID of the chat",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Messages retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Messages retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ChatMessageResource")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User does not belong to the chat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this chat.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function messages(Chat $chat)
    {
        $userId = auth()->id();

        if ($chat->buyer_id !== $userId && $chat->seller_id !== $userId) {
            throw new HttpException(403, 'You do not have access to this chat.');
        }

        $messages = $this->chatMessageService->getPaginatedMessages($chat);

        return $this->success(ChatMessageResource::collection($messages), 'Messages retrieved successfully');
    }

    public function markAsSeen(Chat $chat)
    {
        $user = auth()->user();
        $this->chatMessageService->markMessagesAsSeen($user, $chat);

        return $this->success([], 'Messages marked as seen successfully');
    }
}
