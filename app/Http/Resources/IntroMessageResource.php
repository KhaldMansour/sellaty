<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="IntroMessageSchema",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="image_url", type="string", example="http://localhost:8000/storage/splash_images/Jph0KuayfVBC4szEsoFuGfzoIqJUhPnrJ93qkVCE.png"),
 *     @OA\Property(property="text_message", type="string", example="lknnh"),
 *     @OA\Property(property="active", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T13:16:20.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T13:22:16.000000Z")
 * )
 */
class IntroMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'image_url' => $this->image_url,
            'text_message' => $this->getTranslation('text_message', $locale),
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
