<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserSchema",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=8),
 *     @OA\Property(property="name", type="string", example="test"),
 *     @OA\Property(property="email", type="string", example="he@gmail.com"),
 *     @OA\Property(property="phone_number", type="string", example="+201005594752"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T10:52:17.000000Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T10:52:17.000000Z"),
 * )
 */

class UserResource extends JsonResource
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
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
