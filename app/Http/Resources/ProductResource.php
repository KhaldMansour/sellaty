<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ProductSchema",
 *     type="object",
 *     required={"id", "name", "price", "quantity", "categories", "seller", "images", "created_at", "updated_at"},
 *     @OA\Property(property="id", type="integer", example=1, description="Product ID"),
 *     @OA\Property(
 *         property="name",
 *         type="object",
 *         description="Product name translations",
 *         @OA\Property(property="en", type="string", example="Product Name"),
 *         @OA\Property(property="fr", type="string", example="Nom du produit")
 *     ),
 *     @OA\Property(property="price", type="number", format="float", description="Product price", example=29.99),
 *     @OA\Property(
 *         property="description",
 *         type="object",
 *         description="Product description translations",
 *         @OA\Property(property="en", type="string", example="Product description"),
 *         @OA\Property(property="fr", type="string", example="Description du produit")
 *     ),
 *     @OA\Property(property="brand", type="string", example="Nike"),
 *     @OA\Property(property="model", type="string", example="Air Max"),
 *     @OA\Property(property="duration", type="string", description="Product duration/availability", example="30 days"),
 *     @OA\Property(property="quantity", type="integer", description="Product quantity", example=100),
 *     @OA\Property(
 *         property="condition",
 *         type="object",
 *         description="Product condition translations",
 *         @OA\Property(property="en", type="string", example="New"),
 *         @OA\Property(property="fr", type="string", example="Neuf")
 *     ),
 *     @OA\Property(
 *         property="delivery_options",
 *         type="object",
 *         description="Delivery options",
 *         @OA\Property(property="shipping", type="boolean", example=true),
 *         @OA\Property(property="pickup", type="boolean", example=true),
 *         @OA\Property(property="local_delivery", type="boolean", example=false)
 *     ),
 *     @OA\Property(property="address", type="string", description="Physical address", example="123 Main St"),
 *     @OA\Property(property="country", type="string", example="United States"),
 *     @OA\Property(property="state", type="string", example="California"),
 *     @OA\Property(property="city", type="string", example="Los Angeles"),
 *     @OA\Property(property="longitude", type="number", example="50.36577"),
 *     @OA\Property(property="latitude", type="number", example="38.25478"),
 *     @OA\Property(property="postal_code", type="string", example="90001"),
 *     @OA\Property(property="listed_until", type="string", format="date", example="2023-12-31"),
 *     @OA\Property(property="active", type="boolean", description="Whether the product is active", example=true),
 *     @OA\Property(property="negotiable", type="boolean", description="Whether the price is negotiable", example=true),
 *     @OA\Property(property="deliverable", type="boolean", description="Whether the product can be delivered", example=true),
 *     @OA\Property(property="featured", type="boolean", description="Whether the product is featured", example=true),
 *     @OA\Property(
 *         property="images",
 *         type="array",
 *         items=@OA\Items(ref="#/components/schemas/ProductImageResourceSchema"),
 *         description="List of product images"
 *     ),
 *     @OA\Property(
 *         property="seller",
 *         ref="#/components/schemas/UserSchema",
 *         description="Seller details"
 *     ),
 *     @OA\Property(
 *         property="categories",
 *         type="array",
 *         items=@OA\Items(ref="#/components/schemas/CategorySchema"),
 *         description="Product categories"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Product creation date",
 *         example="2025-03-23T13:49:38"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Product last update date",
 *         example="2025-03-23T13:49:38"
 *     )
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
            'price' => number_format((float) $this->price, 2, '.', ''),
            'description' => $this->getTranslation('description', $locale),
            'type' => 'Product',
            'brand' => $this->brand,
            'model' => $this->model,
            'duration' => $this->duration,
            'quantity' => (int) $this->quantity,
            'currency' => $this->currency,
            'condition' => array_map('strval', (array) $this->condition),
            'delivery_options' => array_map('strval', (array) $this->delivery_options),
            'address' => $this->address,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'listed_until' => $this->listed_until,
            'status' => $this->status,
            'negotiable' => $this->negotiable,
            'deliverable' => $this->deliverable,
            'featured' => $this->featured,
            'images' => ProductImageResource::collection($this->images),
            'seller' => new UserResource($this->seller),
            'categories' => CategoryResource::collection($this->categories),
            'custom_fields' => $this->customFieldValues->map(function ($fieldValue) {
                return [
                    'id' => $fieldValue->custom_field_id,
                    'name' => $fieldValue->customField->name,
                    'type' => $fieldValue->customField->type,
                    'value' => $this->castCustomFieldValue($fieldValue->value, $fieldValue->customField->type),
                ];
            })->values(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    protected function castCustomFieldValue($value, $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($value) ? (float) $value : null,
            'date' => $value ? date('Y-m-d', strtotime($value)) : null,
            default => $value,
        };
    }
}
