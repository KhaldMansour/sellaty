<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ProductResource",
 *     type="object",
 *     required={"id", "name", "price", "quantity", "categories", "seller", "images", "created_at", "updated_at"},
 *     @OA\Property(property="id", type="integer", example=1, description="Product ID"),
 *     @OA\Property(property="name", type="string", description="Product name", example="Product Name"),
 *     @OA\Property(property="price", type="number", format="float", description="Product price", example=29.99),
 *     @OA\Property(property="description", type="string", description="Product description", example="This is a product description"),
 *     @OA\Property(property="quantity", type="integer", description="Product quantity", example=100),
 *     @OA\Property(property="featured", type="boolean", description="Whether the product is featured", example=true),
 *     @OA\Property(property="images", type="array", items=@OA\Items(ref="#/components/schemas/ProductImageResourceSchema"), description="List of product images"),
 *     @OA\Property(property="seller", ref="#/components/schemas/UserSchema", description="Seller details"),
 *     @OA\Property(property="categories", type="array", items=@OA\Items(ref="#/components/schemas/CategorySchema"), description="Product categories"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Product creation date", example="2025-03-23T13:49:38"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Product last update date", example="2025-03-23T13:49:38")
 * )
 */
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
            'images' => ProductImageResource::collection($this->images),
            'seller' => new UserResource($this->seller),
            'categories' => CategoryResource::collection($this->categories),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
