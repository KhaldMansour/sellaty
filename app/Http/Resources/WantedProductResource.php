<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="WantedProductSchema",
 *     type="object",
 *     required={"id", "name", "description", "brand", "model", "duration", "min_price", "max_price", "condition", "delivery_options", "address", "country", "state", "city", "postal_code", "listed_until", "active", "user", "images"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="The ID of the wanted product"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The name of the wanted product",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description of the wanted product",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="brand",
 *         type="string",
 *         description="The brand of the wanted product",
 *         maxLength=255,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="model",
 *         type="string",
 *         description="The model of the wanted product",
 *         maxLength=255,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="duration",
 *         type="string",
 *         description="The duration for the wanted product (e.g., '2 weeks')",
 *         pattern="^\d+\s(?:week|weeks|day|days)$"
 *     ),
 *     @OA\Property(
 *         property="min_price",
 *         type="number",
 *         description="The minimum price of the wanted product",
 *         format="float",
 *         minimum=0
 *     ),
 *     @OA\Property(
 *         property="max_price",
 *         type="number",
 *         description="The maximum price of the wanted product",
 *         format="float",
 *         minimum=0
 *     ),
 *     @OA\Property(
 *         property="condition",
 *         type="array",
 *         description="Condition of the wanted product (e.g., new, used)",
 *         @OA\Items(type="string")
 *     ),
 *     @OA\Property(
 *         property="delivery_options",
 *         type="array",
 *         description="Delivery options for the wanted product",
 *         @OA\Items(type="string")
 *     ),
 *     @OA\Property(
 *         property="address",
 *         type="string",
 *         description="The address of the buyer",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="country",
 *         type="string",
 *         description="The country where the buyer is located",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="state",
 *         type="string",
 *         description="The state where the buyer is located",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="city",
 *         type="string",
 *         description="The city where the buyer is located",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="postal_code",
 *         type="string",
 *         description="Postal code of the buyer's address",
 *         maxLength=6
 *     ),
 *     @OA\Property(
 *         property="listed_until",
 *         type="string",
 *         description="The date when the product will be listed until",
 *         format="date-time"
 *     ),
 *     @OA\Property(
 *         property="active",
 *         type="boolean",
 *         description="The active status of the wanted product"
 *     ),
 *     @OA\Property(
 *         property="longitude",
 *         type="number",
 *         format="float",
 *         description="Longitude coordinate of the product location"
 *     ),
 *     @OA\Property(
 *         property="latitude",
 *         type="number",
 *         format="float",
 *         description="Latitude coordinate of the product location"
 *     ),
 *     @OA\Property(
 *         property="user",
 *         description="The user who created the wanted product",
 *         ref="#/components/schemas/UserSchema"
 *     ),
 *     @OA\Property(
 *         property="images",
 *         type="array",
 *         description="List of images for the wanted product",
 *         @OA\Items(
 *             type="string",
 *             format="binary"
 *         )
 *     )
 * )
 */
class WantedProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $locale = app()->getLocale();
        $fallbackLocale = $locale === 'en' ? 'ar' : 'en';

        $name = $this->getTranslation('name', $locale);
        if (empty($name)) {
            $name = $this->getTranslation('name', $fallbackLocale);
        }

        $description = $this->getTranslation('description', $locale);
        if (empty($description)) {
            $description = $this->getTranslation('description', $fallbackLocale);
        }

        return [
            'id' => $this->id,
            'name' => $name,
            'description' => $description,
            'status' => __('messages.product_status_' . $this->status),
            'type' => 'WantedProduct',
            'brand' => $this->brand,
            'model' => $this->model,
            'duration' => $this->duration,
            'min_price' => (float) $this->min_price,
            'max_price' => (float) $this->max_price,
            'currency' => $this->currency,
            'condition' => array_map('strval', (array) $this->condition),
            'delivery_options' => array_map('strval', (array) $this->delivery_options),
            'address' => $this->address,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'latitude' => $this->formatCoordinate($this->latitude),
            'longitude' => $this->formatCoordinate($this->longitude),
            'postal_code' => $this->postal_code,
            'listed_until' => $this->listed_until,
            'categories' => CategoryResource::collection($this->categories),
            'user' => new UserResource($this->buyer),
            'images' => WantedProductImageResource::collection($this->images),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    private function formatCoordinate($value): float
    {
        if ($value === null) {
            return 0.0000001;
        }

        $value = (float) $value;
        $epsilon = 0.0001;

        if (fmod($value, 1.0) === 0.0) {
            $value += $epsilon;
        }

        return $value;
    }
}
