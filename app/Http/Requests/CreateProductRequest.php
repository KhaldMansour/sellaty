<?php

namespace App\Http\Requests;

use App\Models\CustomField;
use App\Models\Product;

/**
 * @OA\Schema(
 *     schema="CreateProductRequestSchema",
 *     type="object",
 *     required={"name", "price", "quantity", "condition[]" , "address", "country", "state", "city", "postal_code" , "category_ids[]" , "delivery_options[]" , "images[]" , "duration" , "city_lat" , "city_long" , "custom_fields"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Product Name"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="Product Description"
 *     ),
 *     @OA\Property(property="brand", type="string", maxLength=255, example="Nike"),
 *     @OA\Property(property="model", type="string", maxLength=255, example="Air Max"),
 *     @OA\Property(property="price", type="number", format="float", example=99.99),
 *     @OA\Property(property="duration", type="string", description="Product duration/availability", example="30 days"),
 *     @OA\Property(property="quantity", type="integer", minimum=0, example=10),
 *     @OA\Property(
 *         property="condition[]",
 *         type="array",
 *         @OA\Items(type="string"),
 *         example={"new"}
 *     ),
 *     @OA\Property(
 *         property="delivery_options[]",
 *         type="array",
 *         @OA\Items(type="string"),
 *         example={"Pickup"}
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
 *     @OA\Property(property="address", type="string", description="Physical address", example="123 Main St"),
 *     @OA\Property(property="country", type="string", example="United States"),
 *     @OA\Property(property="state", type="string", example="California"),
 *     @OA\Property(property="city", type="string", example="Los Angeles"),
 *     @OA\Property(property="city_lat", type="number", format="float", example=-59.9984268),
 *     @OA\Property(property="city_long", type="number", format="float", example=38.958749),
 *     @OA\Property(property="latitude", type="number", format="float", example=-59.9984268),
 *     @OA\Property(property="longitude", type="number", format="float", example=38.958749),
 *     @OA\Property(property="postal_code", type="string", example="90001"),
 *     @OA\Property(
 *         property="active",
 *         type="integer",
 *         enum={0, 1},
 *         example=1,
 *         description="Whether the product is active (1 = yes, 0 = no)"
 *     ),
 *     @OA\Property(
 *         property="negotiable",
 *         type="integer",
 *         enum={0, 1},
 *         example=1,
 *         description="Whether the product is negotiable (1 = yes, 0 = no)"
 *     ),
 *     @OA\Property(
 *         property="deliverable",
 *         type="integer",
 *         enum={0, 1},
 *         example=1,
 *         description="Whether the product is deliverable (1 = yes, 0 = no)"
 *     ),
 *     @OA\Property(
 *         property="category_ids[]",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         description="Array of category IDs",
 *         example={1}
 *     ),
 *     @OA\Property(
*         property="custom_fields",
*         type="object",
*         description="Key-value pairs of custom field IDs and their values. Keys are field IDs as strings.",
*         example={
*             "1": "Audi",
*             "2": "ILX",
*             "3": true
*         },
*         @OA\AdditionalProperties(
*             type="string",
*             description="Value for the custom field. Type depends on field configuration (text, number, boolean, etc.)."
*         )
*     ),
 * )
 */
class CreateProductRequest extends BaseFormRequest
{
    protected $customFieldsMap = [];


    protected function getCustomFields()
    {
        if (empty($this->customFieldsMap)) {
            $categoryIds = $this->input('category_ids', []);

            $this->customFieldsMap = CustomField::whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })->get();
        }

        return $this->customFieldsMap;
    }
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
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'condition' => 'required|array',
            'condition.*' => 'string|in:New,Used',
            'delivery_options' => 'required|array',
            'delivery_options.*' => 'string|in:Meet up,Pickup',
            'images' => 'required|array|max:20',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:20048',
            'address' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|digits:5',
            'negotiable' => 'nullable|boolean',
            'deliverable' => 'nullable|boolean',
            'category_ids' => 'required|array',
            'category_ids.*' => 'numeric|exists:categories,id',
            'currency' => 'required|string|max:3',
            'city_lat' => 'required|numeric',
            'city_long' => 'required|numeric',
            'longitude' => 'numeric',
            'latitude' => 'numeric',
            'custom_fields' => 'present|array',
        ];

        return $rules;
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $customFieldsInput = $this->input('custom_fields', []);
            $customFieldsMap = $this->getCustomFields();
            foreach ($customFieldsMap as $field) {
                $key = $field->id;
                $value = $customFieldsInput[$key] ?? null;


                if ($field->required && is_null($value)) {
                    $validator->errors()->add("custom_fields.$key", "custom field {$field->name} is required.");
                    continue;
                }

                if (is_null($value)) {
                    continue;
                }

                switch ($field->type) {
                    case 'text':
                        if (!is_string($value)) {
                            $validator->errors()->add("custom_fields.$key", "custom field {$field->name} must be a string.");
                        }
                        break;

                    case 'number':
                        if (!filter_var($value, FILTER_VALIDATE_INT)) {
                            $validator->errors()->add("custom_fields.$key", "custom field {$field->name} must be a number.");
                        }
                        break;

                    case 'boolean':
                        if (!in_array($value, [true, false, 0, 1, '0', '1'], true)) {
                            $validator->errors()->add("custom_fields.$key", "{$field->name} must be boolean.");
                        }
                        break;

                    case 'select':
                        $validOptions = $field->options->pluck('value')->toArray();
                        if (!in_array($value, $validOptions)) {
                            $validator->errors()->add("custom_fields.$key", "custom field {$field->name} must be one of: " . implode(', ', $validOptions));
                        }
                        break;

                    default:
                        $validator->errors()->add("custom_fields.$key", "Unknown type for {$field->name}.");
                }
            }
        });
    }



    public function messages()
    {
        $messages = [
            'category_ids.*.exists' => 'The category ID :input is invalid. Please select a valid category.',
        ];

        return $messages;
    }

    protected function prepareForValidation()
    {
        if ($this->input('custom_fields') && is_string($this->input('custom_fields'))) {
            $decoded = json_decode($this->input('custom_fields'), true);
            if (is_array($decoded)) {
                $allowedFieldIds = collect($this->getCustomFields())
                    ->pluck('id')
                    ->map(fn ($id) => $id)
                    ->toArray();

                $filtered = array_filter(
                    $decoded,
                    fn ($value) => in_array($value, $allowedFieldIds, true),
                    ARRAY_FILTER_USE_KEY
                );

                $this->merge([
                    'custom_fields' => $filtered,
                ]);
            }
        }

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
