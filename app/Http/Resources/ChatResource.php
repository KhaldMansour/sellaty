<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ChatSchema",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="product_id", type="integer", example=1),
 *     @OA\Property(property="buyer_id", type="integer", example=29),
 *     @OA\Property(property="seller_id", type="integer", example=23),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-03T22:45:43.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-03T22:45:43.000000Z"),
 *     @OA\Property(property="unseen_messages_count", type="integer", example=12),
 *     @OA\Property(property="product_name", type="string", example="doloremque"),
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

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'unseen_messages_count' => $this->unseen_messages_count,
            'product_name' => $this->name,
            'counterpart' => [
                'id' => $counterpart->id,
                'name' => $counterpart->name,
            ],
        ];
    }
}
