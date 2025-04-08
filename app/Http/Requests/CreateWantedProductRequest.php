<?php

namespace App\Http\Requests;

use App\Models\WantedProduct;
use Illuminate\Foundation\Http\FormRequest;

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
 *         "postal_code"
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
 *     @OA\Property(
 *         property="duration",
 *         type="string",
 *         description="Duration for the wanted product (e.g., '2 weeks')",
 *         pattern="^\d+\s(?:week|weeks|day|days)$"
 *     ),
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
 *         property="condition[]",
 *         type="array",
 *         description="Condition of the wanted product (e.g., new, used)",
 *         @OA\Items(type="string", example="new")
 *     ),
 *     @OA\Property(
 *         property="delivery_options[]",
 *         type="array",
 *         description="Delivery options for the wanted product",
 *         @OA\Items(type="string", example="shipping")
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
class CreateWantedProductRequest extends FormRequest
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
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($locale) {
                    $productExists = WantedProduct::where('name->' . $locale, '=', $value)
                        ->exists();

                    if ($productExists) {
                        $fail("The wanted product name for this locale is already taken.");
                    }
                }
            ],
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'duration' => ['required','regex:/^\\d+\\s(?:week|weeks|day|days)$/'],
            'min_price' => 'required|numeric|min:0',
            'max_price' => 'required|numeric|min:0|gte:min_price',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'condition' => 'required|array',
            'delivery_options' => 'required|array',
            'address' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:6',
            'active' => 'nullable|boolean',
        ];
    }
}
