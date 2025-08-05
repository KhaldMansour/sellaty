<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ChatSchema",
 *     type="object",
 *     title="Chat Resource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Product Chat"),
 *     @OA\Property(property="buyer_id", type="integer", example=29),
 *     @OA\Property(property="seller_id", type="integer", example=23),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-03T22:45:43.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-03T22:45:43.000000Z"),
 *
 *     @OA\Property(
 *         property="product",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Product Name"),
 *         @OA\Property(property="image", type="string", example="https://example.com/image.jpg")
 *     ),
 *
 *     @OA\Property(
 *         property="latestOffer",
 *         oneOf={
 *             @OA\Schema(ref="#/components/schemas/OfferSchema"),
 *             @OA\Schema(type="null")
 *         }
 *     ),
 *     @OA\Property(
 *         property="latest_message",
 *         oneOf={
 *             @OA\Schema(ref="#/components/schemas/ChatMessageSchema"),
 *             @OA\Schema(type="null")
 *         }
 *     ),
 *     @OA\Property(property="unseen_messages_count", type="integer", example=12),
 *
 *     @OA\Property(
 *         property="counterpart",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=23),
 *         @OA\Property(property="name", type="string", example="John Seller")
 *     )
 * )
 */
class ChatResource extends JsonResource
{
    public function toArray($request)
    {
        $user = auth()->user();

        $isBuyer = $user->id === $this->buyer_id;

        $counterpart = $isBuyer ? $this->seller : $this->buyer;

        // dd(($this->chatable instanceof Product) , $this->chatable);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'product' => ($this->chatable instanceof Product) ? new ProductResource($this->chatable) : new WantedProductResource($this->chatable),
            'latestOffer' => new OfferResource($this->latestOffer),
            'unseen_messages_count' => $this->unseen_messages_count,
            'counterpart' => [
                'id' => $counterpart->id,
                'name' => $counterpart->full_name,
            ],
            'latest_message' => new ChatMessageResource($this->latestMessage),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
