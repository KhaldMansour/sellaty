<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentSearchResource extends JsonResource
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
            'user_id' => $this->user_id,
            'field' => $this->field,
            'value' => $this->value,
            'type' => $this-> model === Product::class ? 'seller' : 'buyer',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
