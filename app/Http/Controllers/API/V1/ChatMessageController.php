<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendChatMessageRequest;
use App\Http\Resources\ChatMessageResource;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Services\ChatMessageService;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
     *             @OA\Property(property="data", ref="#/components/schemas/ChatMessageSchema")
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
        try {
            $message = $this->chatService->sendMessage($chat, auth()->user(), $request->validated());

            return $this->success(new ChatMessageResource($message), 'Message sent successfully', 201);
        } catch (\Exception $e) {
            throw new HttpException(400, 'Failed to send message.');
        }
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
     *                 @OA\Items(ref="#/components/schemas/ChatMessageSchema")
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
        $user = auth()->user();

        if (!in_array($user->id, $chat->users->pluck('id')->toArray())) {
            throw new HttpException(403, __('messages.no_chat_access'));
        }

        $messages = $this->chatMessageService->getPaginatedMessages($chat);

        return $this->success(ChatMessageResource::collection($messages), __('messages.messages_retrieved'));
    }

    public function markAsSeen(Chat $chat)
    {
        $user = auth()->user();
        $this->chatMessageService->markMessagesAsSeen($user, $chat);

        return $this->success([], 'Messages marked as seen successfully');
    }

    public function getMedia(ChatMessage $chatMessage)
    {
        $chat = Chat::with(['buyer', 'seller'])->find($chatMessage->chat_id);

        $user = auth()->user();

        if (!$chat || !$user || !in_array($user?->id, $chat->users->pluck('id')->toArray())) {
            abort(403, __('messages.not_authorized_to_access_chat'));
        }

        $filePath = $chatMessage->content;


        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, __('messages.file_not_found'));
        }

        $mimeType = Storage::disk('local')->mimeType($filePath);

        return response()->file(storage_path('app/private/' . $filePath), [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'max-age=3600, public',
        ]);
    }
}
