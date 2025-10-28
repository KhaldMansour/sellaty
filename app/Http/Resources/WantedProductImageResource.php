<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WantedProductImageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'url' => $this->image_url,
        ];
    }
}
