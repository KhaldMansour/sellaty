<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="OfferSchema",
 *     type="object",
 *     title="Offer",
 *     description="An offer made by a user on a product",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="text", type="string", example="I would like to offer $150 for this item."),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="price", type="number", format="float", example=150.00),
 *     @OA\Property(property="user", ref="#/components/schemas/UserSchema"),
 *     @OA\Property(property="product_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-03T22:45:43Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-04T12:01:11Z")
 * )
 */
class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'price' => $this->price,
            'user' => new UserResource($this->user),
            'status' => $this->status,
            'product' => $this->product_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
