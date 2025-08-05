<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CustomFieldSchema",
 *     type="object",
 *     title="Custom Field",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="category_id", type="integer", example=2),
 *     @OA\Property(property="name", type="string", example="Color"),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         enum={"text", "number", "boolean", "date"},
 *         example="text"
 *     ),
 *     @OA\Property(property="required", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-08T14:22:33Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-08T14:22:33Z")
 * )
 */
class CustomFieldResource extends JsonResource
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
            'category_id' => $this->category_id,
            'name' => $this->name,
            'type' => $this->type,
            'required' => (bool) $this->required,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
