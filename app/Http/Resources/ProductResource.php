<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->getTranslation('name', $locale),
            'price' => $this->price,
            'description' => $this->getTranslation('description', $locale),
            'quantity' => $this->quantity,
            'featured' => $this->featured,
            'image_url' => $this->image_url,
            'seller' => new UserResource($this->seller),
            'categories' => CategoryResource::collection($this->categories), 
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
