<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CategorySchema",
 *     type="object",
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="test"),
 *         @OA\Property(property="description", type="string", example="hello"),
 *         @OA\Property(property="imgae_url", type="string"),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-13T05:53:52.000000Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-13T05:53:52.000000Z")
 *     }
 * )
 */
class CategoryResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'products_count' => $this->products_count,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
