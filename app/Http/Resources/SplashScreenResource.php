<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SplashScreenSchema",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="image_url", type="string", example="http://localhost:8000/storage/splash_images/Jph0KuayfVBC4szEsoFuGfzoIqJUhPnrJ93qkVCE.png"),
 *     @OA\Property(property="display_time", type="string", nullable=true, example=null),
 *     @OA\Property(property="text_message", type="string", example="lknnh"),
 *     @OA\Property(property="active", type="integer", example=1),
 *     @OA\Property(property="display_order", type="integer", nullable=true, example=null),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T13:16:20.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T13:22:16.000000Z")
 * )
 */
class SplashScreenResource extends JsonResource
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
            'display_time' => $this->display_time,
            'text_message' => $this->getTranslation('text_message', $locale),
            'active' => $this->active,
            'display_order' => $this->display_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
