<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserSchema",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=8),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="username", type="string", example="JohnDoe"),
 *     @OA\Property(property="full_name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *     @OA\Property(
 *         property="profile_photo",
 *         type="string",
 *         format="url",
 *         nullable=true,
 *         example="http://yourdomain.com/storage/users/profile.jpg"
 *     ),
 *     @OA\Property(property="phone_number", type="string", example="+201005594752"),
 *     @OA\Property(property="location", type="string", example="Cairo, Egypt", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T10:52:17.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T10:52:17.000000Z")
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'username' => $this->username,
            'email' => $this->email,
            'profile_photo' => $this->profile_photo,
            'phone_number' => $this->phone_number,
            'location' => $this->location,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
