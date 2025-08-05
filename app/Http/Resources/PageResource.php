<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="PageSchema",
 *     type="object",
 *     title="Page",
 *     description="A static page resource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="About Us"),
 *     @OA\Property(property="slug", type="string", example="about-us"),
 *     @OA\Property(property="content", type="string", example="<p>This is about us content</p>"),
 *     @OA\Property(property="published", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-05-26T14:20:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-05-27T14:20:00Z"),
 * )
 */
class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', $locale),
            'slug' => $this->slug,
            'content' => $this->getTranslation('content', $locale),
            'published' => $this->published,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
