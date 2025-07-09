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
 *         @OA\Property(property="name", type="string", example="Cars"),
 *         @OA\Property(property="description", type="string", example="All car listings"),
 *         @OA\Property(property="image_url", type="string", example="https://example.com/storage/categories/car.jpg"),
 *         @OA\Property(property="products_count", type="integer", example=42),
 *         @OA\Property(
 *             property="custom_fields",
 *             type="array",
 *             description="List of custom fields for this category",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Car Make"),
 *                 @OA\Property(property="type", type="string", example="select"),
 *                 @OA\Property(property="required", type="boolean", example=true),
 *                 @OA\Property(
 *                     property="options",
 *                     type="array",
 *                     @OA\Items(type="string", example="Toyota")
 *                 )
 *             )
 *         ),
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
            'custom_fields' => $this->customFields,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
