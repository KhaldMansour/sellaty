<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="NotificationSchema",
 *     type="object",
 *     title="Notification",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=42),
 *     @OA\Property(property="title", type="string", example="Order Shipped"),
 *     @OA\Property(property="body", type="string", example="Your order #1234 has been shipped."),
 *     @OA\Property(property="data", type="object"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-30T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-30T13:00:00Z")
 * )
 */
class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        $locale = app()->getLocale() ?? auth()->user()?->locale;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->getTranslation('title', $locale),
            'body' => $this->getTranslation('body', $locale),
            'data' => $this->data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
