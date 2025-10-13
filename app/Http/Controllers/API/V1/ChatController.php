<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateChatRequest;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use App\Models\Product;
use App\Services\ChatService;
use Illuminate\Auth\Access\AuthorizationException;

class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chatService)
    {
    }

    public function index()
    {
        $chat = Chat::find(1);

        return view('test-chat', ['chat' => $chat]);
    }

    public function indexTest()
    {
        return view('test-chat');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chats/{chatId}",
     *     summary="Get a specific chat",
     *     description="Retrieve details of a specific chat by ID for the authenticated user.",
     *     operationId="getChatById",
     *     tags={"Chats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="chatId",
     *         in="path",
     *         description="ID of the chat to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chat retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example=null),
     *             @OA\Property(property="data", ref="#/components/schemas/ChatSchema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chat not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Chat not found.")
     *         )
     *     )
     * )
     */
    public function show(int $chatId)
    {
        $user = auth()->user();

        $chat = $this->chatService->getChat($chatId, $user->id);

        return $this->success(new ChatResource($chat));
    }


    /**
     * @OA\Post(
     *     path="/api/v1/chats/products/{productId}",
     *     summary="Get or create a chat for the product",
     *     description="Get an existing chat or create a new one between the authenticated buyer and the product's seller.",
     *     operationId="getOrCreateChat",
     *     tags={"Chats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="ID of the product",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chat retrieved or created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example=null),
     *             @OA\Property(property="data", ref="#/components/schemas/ChatSchema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found.")
     *         )
     *     )
     * )
     */

    public function getOrCreate(CreateChatRequest $request, int $id)
    {
        $userId = auth()->id();

        $model = $request->resource();

        $resource = $model::withTrashed()->findOrFail($id);

        $chat = $this->chatService->getOrCreateChat($resource, $userId);

        return $this->success(new ChatResource($chat));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chats/buyer",
     *     summary="Get chats where the authenticated user is the buyer",
     *     description="Returns a list of chats where the authenticated user is the buyer, including unseen message counts and counterpart (seller) information.",
     *     operationId="getBuyerChats",
     *     tags={"Chats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of buyer chats retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ChatSchema")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function buyerChats()
    {
        $userId = auth()->id();
        $buyerChats = $this->chatService->getBuyerChatsWithUnseenCount($userId);

        return $this->success(ChatResource::collection($buyerChats));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chats/seller",
     *     summary="Get chats where the authenticated user is the seller",
     *     description="Returns a list of chats where the authenticated user is the seller, including unseen message counts and counterpart (buyer) information.",
     *     operationId="getSellerChats",
     *     tags={"Chats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of seller chats retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ChatSchema")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function sellerChats()
    {
        $userId = auth()->id();

        $sellerChats = $this->chatService->getSellerChatsWithUnseenCount($userId);

        return $this->success(ChatResource::collection($sellerChats));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chats/my-chats",
     *     summary="Get all chats of the current user",
     *     description="Returns a list of chats of the authenticated .",
     *     operationId="getAllChats",
     *     tags={"Chats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of seller chats retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ChatSchema")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function myChats()
    {
        $userId = auth()->id();
        $chats = $this->chatService->getChatsWithUnseenCount($userId);

        return $this->success(ChatResource::collection($chats));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chats/{chat}/mark-as-seen",
     *     summary="Mark all messages in a chat as seen",
     *     description="Marks all unseen messages in the specified chat as seen for the authenticated user.",
     *     tags={"Chats"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="chat",
     *         in="path",
     *         required=true,
     *         description="ID of the chat to mark messages as seen",
     *         @OA\Schema(type="integer", example=42)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Messages marked as seen successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Messages marked as seen."),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to access this chat",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to access this chat.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Chat not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Chat not found.")
     *         )
     *     )
     * )
     */
    public function markAsSeen(Chat $chat)
    {
        try {
            $this->authorize('access', $chat);
        } catch (AuthorizationException $e) {
            return $this->failure('You are not authorized to access this chat.', 403);
        }

        $userId = auth()->id();
        $this->chatService->markMessagesAsSeen($chat, $userId);

        return $this->success(null, 'Messages marked as seen.');
    }
}
