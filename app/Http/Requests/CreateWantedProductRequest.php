<?php

namespace App\Http\Requests;

/**
 * @OA\Schema(
 *     schema="CreateWantedProductRequestSchema",
 *     type="object",
 *     required={
 *         "duration",
 *         "min_price",
 *         "max_price",
 *         "condition[]",
 *         "delivery_options[]",
 *         "address",
 *         "country",
 *         "state",
 *         "city",
 *         "postal_code",
 *         "city_lat",
 *         "city_long",
 *         "category_ids[]",
 *         "currency",
 *     },
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The name of the wanted product",
 *         maxLength=255,
 *         nullable=true
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
 *     @OA\Property(property="duration", type="string", description="Product duration/availability", example="30 days"),
 *     @OA\Property(
 *         property="min_price",
 *         type="number",
 *         description="Minimum price for the wanted product",
 *         format="float",
 *         minimum=0
 *     ),
 *     @OA\Property(
 *         property="max_price",
 *         type="number",
 *         description="Maximum price for the wanted product",
 *         format="float",
 *         minimum=0
 *     ),
 *     @OA\Property(
 *         property="currency",
 *         type="string",
 *         description="Currency code (e.g., USD, SAR)",
 *         maxLength=3,
 *         example="SAR"
 *     ),
 *     @OA\Property(
 *         property="category_ids[]",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         description="Array of category IDs",
 *         example={1}
 *     ),
 *     @OA\Property(
 *         property="condition[]",
 *         type="array",
 *         description="Condition of the wanted product (e.g., new, used)",
 *         @OA\Items(type="string", example="New")
 *     ),
 *     @OA\Property(
 *         property="delivery_options[]",
 *         type="array",
 *         @OA\Items(type="string"),
 *         example={"Pickup"}
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
 *         property="city_lat",
 *         type="number",
 *         format="float",
 *         description="Latitude coordinate of the city"
 *     ),
 *     @OA\Property(
 *         property="city_long",
 *         type="number",
 *         format="float",
 *         description="Longitude coordinate of the city"
 *     ),
 *     @OA\Property(
 *         property="latitude",
 *         type="number",
 *         format="float",
 *         description="Latitude coordinate of the specific location"
 *     ),
 *     @OA\Property(
 *         property="longitude",
 *         type="number",
 *         format="float",
 *         description="Longitude coordinate of the specific location"
 *     ),
 *     @OA\Property(
 *         property="postal_code",
 *         type="string",
 *         description="Postal code of the buyer's address",
 *         maxLength=6,
 *         example="12345"
 *     ),
 *     @OA\Property(
 *         property="images[]",
 *         type="array",
 *         description="Product images (array of files)",
 *         @OA\Items(
 *             type="string",
 *             format="binary"
 *         )
 *     ),
 *     @OA\Property(
 *         property="active",
 *         type="integer",
 *         enum={0, 1},
 *         description="Is the product active (0 = false, 1 = true)",
 *         example=1
 *     )
 * )
 */
class CreateWantedProductRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $locale = app()->getLocale();

        return [
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'duration' => ['required','regex:/^\\d+\\s(?:week|weeks|day|days|month|months)$/'],
            'min_price' => 'required|numeric|min:0',
            'max_price' => 'required|numeric|min:0|gte:min_price',
            'images' => 'required|array|max:20',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:20048',
            'condition' => 'required|array',
            'condition.*' => 'string|in:New,Used',
            'delivery_options' => 'required|array',
            'delivery_options.*' => 'string|in:Meet up,Pickup',
            'address' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'sometimes|string|max:255',
            'city_lat' => 'required|numeric',
            'city_long' => 'required|numeric',
            'longitude' => 'numeric',
            'latitude' => 'numeric',
            'postal_code' => 'required|digits:5',
            'currency' => 'required|string|max:3',
            'category_ids' => 'required|array',
            'category_ids.*' => 'numeric|exists:categories,id',
        ];
    }

    protected function prepareForValidation()
    {
        $latitude = $this->input('latitude');
        $longitude = $this->input('longitude');


        if (is_null($latitude) || $latitude == 0.0) {
            $this->merge([
                'latitude' => $this->input('city_lat'),
            ]);
        }

        if (is_null($longitude) || $longitude == 0.0) {
            $this->merge([
                'longitude' => $this->input('city_long'),
            ]);
        }
    }
}
