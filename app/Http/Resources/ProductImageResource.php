<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ProductImageResourceSchema",
 *     type="object",
 *     required={"id", "product_id", "url", "created_at", "updated_at"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="product_id", type="integer", example=1),
 *     @OA\Property(property="url", type="string", example="http://localhost/storage/products/sample-image.jpg"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-23T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-23T12:34:56Z")
 * )
 */
class ProductImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'url' => $this->image_url,
            'thumbnail' => $this->thumbnail_path,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
