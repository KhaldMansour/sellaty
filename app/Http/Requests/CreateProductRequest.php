<?php

namespace App\Http\Requests;

use App\Models\CustomField;
use App\Models\CustomFieldOption;
use App\Models\Product;

/**
 * @OA\Schema(
 *     schema="CreateProductRequestSchema",
 *     type="object",
 *     required={"name", "price", "quantity", "condition[]" , "address", "country", "state", "city", "postal_code" , "category_ids[]" , "delivery_options[]" , "images[]" , "duration" , "city_lat" , "city_long"},
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
 *     )
 * )
 */
class CreateProductRequest extends BaseFormRequest
{
    protected $customFieldsMap = [];


    protected function getCustomFields()
    {
        if (empty($this->customFieldsMap)) {
            $this->customFieldsMap = CustomField::whereIn('category_id', $this->input('category_ids', []))->get();
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
            'custom_fields' => 'required|array',
        ];

        if ($this->has('category_ids') && is_array($this->category_ids)) {
            $customFields = $this->getCustomFields();


            foreach ($customFields as $field) {
                $key = "custom_fields.{$field->id}";
                $baseRule = $field->required ? 'required' : 'nullable';

                $fieldRule = match ($field->type) {
                    'text' => [$baseRule, 'string'],
                    'number' => [$baseRule, 'numeric'],
                    'boolean' => [$baseRule, 'boolean'],
                    'date' => [$baseRule, 'date'],
                    'select' => [
                        $baseRule,
                        'string',
                        function ($attribute, $value, $fail) use ($field) {
                            $exists = CustomFieldOption::where('custom_field_id', $field->id)
                                ->where('value', $value)
                                ->exists();

                            if (! $exists) {
                                $fail("The selected option for '{$field->name}' is invalid.");
                            }
                        },
                    ],
                    default => ['nullable'],
                };

                $rules[$key] = $fieldRule;
            }
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'category_ids.*.exists' => 'The category ID :input is invalid. Please select a valid category.',
        ];

        if ($this->has('category_ids') && is_array($this->category_ids)) {
            $customFields = $this->getCustomFields();

            foreach ($customFields as $field) {
                $keyBase = "custom_fields.{$field->id}";

                $messages["{$keyBase}.required"] = "The ". $field->name ." field in custom fields is required.";
                $messages["{$keyBase}.string"] = "The ". $field->name ." field must be a string.";
                $messages["{$keyBase}.numeric"] = "The  ". $field->name ." field must be a number.";
                $messages["{$keyBase}.boolean"] = "The  ". $field->name ."  field must be true or false.";
                $messages["{$keyBase}.date"] = "The  ". $field->name ." field must be a valid date.";
            }
        }

        return $messages;
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
