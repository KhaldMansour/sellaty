<?php

namespace App\Http\Requests;

use App\Models\CustomField;
use Illuminate\Support\Facades\Validator;

class UpdateProductRequest extends BaseFormRequest
{
    protected $customFieldsMap = [];

    protected function getCustomFields()
    {
        if (empty($this->customFieldsMap)) {
            $categoryIds = $this->input('category_ids', []);

            $this->customFieldsMap = CustomField::when(!empty($categoryIds), function ($query) use ($categoryIds) {
                $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
            })->get();
        }

        return $this->customFieldsMap;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'duration' => ['sometimes', 'required', 'regex:/^\\d+\\s(?:week|weeks|day|days|month|months)$/'],
            'quantity' => 'sometimes|required|integer|min:0',
            'condition' => 'sometimes|required|array',
            'condition.*' => 'string|in:New,Used',
            'delivery_options' => 'sometimes|required|array',
            'delivery_options.*' => 'string|in:Meet up,Pickup',
            'images' => 'nullable|array|max:20',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:20048',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|digits:5',
            'negotiable' => 'nullable|boolean',
            'deliverable' => 'nullable|boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'numeric|exists:categories,id',
            'currency' => 'nullable|string|max:3',
            'city_lat' => 'nullable|numeric',
            'city_long' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
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

                if ($field->required && $this->has("custom_fields.$key") && is_null($value)) {
                    $validator->errors()->add("custom_fields.$key", "Custom field '{$field->name}' is required.");
                    continue;
                }

                if (is_null($value)) {
                    continue;
                }

                $rules = match ($field->type) {
                    'text' => ['string'],
                    'number' => ['integer'],
                    'boolean' => ['boolean'],
                    'select' => ['in:' . implode(',', $field->options->pluck('value')->toArray())],
                    'year' => [
                        'string',
                        'regex:/^\d{4}$/',
                        function ($attribute, $value, $fail) {
                            $year = (int) $value;
                            $minYear = 1900;
                            $maxYear = date('Y') + 1;

                            if ($year < $minYear || $year > $maxYear) {
                                $fail("The $attribute must be between $minYear and $maxYear.");
                            }
                        }
                    ],
                    default => ['nullable'],
                };

                if (!empty($field->validation_rules)) {
                    $customRules = is_array($field->validation_rules)
                        ? $field->validation_rules
                        : json_decode($field->validation_rules, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($customRules)) {
                        $rules = array_merge($rules, $customRules);
                    }
                }

                $customFieldValidator = Validator::make(['value' => $value], ['value' => $rules]);

                if ($customFieldValidator->fails()) {
                    $validator->errors()->add("custom_fields.$key", $customFieldValidator->errors()->first('value'));
                }
            }
        });
    }

    public function messages()
    {
        return [
            'category_ids.*.exists' => 'The category ID :input is invalid. Please select a valid category.',
        ];
    }

    protected function prepareForValidation()
    {
        $customFields = $this->input('custom_fields');

        if (empty($customFields)) {
            $this->merge(['custom_fields' => []]);
        } elseif (is_string($customFields)) {
            $decoded = json_decode($customFields, true);
            $customFields = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        if (is_array($customFields)) {
            $allowedFieldIds = collect($this->getCustomFields())->pluck('id')->toArray();
            $filtered = array_filter(
                $customFields,
                fn ($value, $key) => in_array($key, $allowedFieldIds, true),
                ARRAY_FILTER_USE_BOTH
            );
            $this->merge(['custom_fields' => $filtered]);
        }

        $latitude = $this->input('latitude');
        $longitude = $this->input('longitude');

        if (is_null($latitude) && $this->filled('city_lat')) {
            $this->merge(['latitude' => $this->input('city_lat')]);
        }

        if (is_null($longitude) && $this->filled('city_long')) {
            $this->merge(['longitude' => $this->input('city_long')]);
        }
    }
}
