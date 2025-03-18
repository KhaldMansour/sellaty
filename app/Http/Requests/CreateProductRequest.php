<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
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
                    $productExists = Product::where('name->' . $locale, '=', $value)
                        ->exists();

                    if ($productExists) {
                        $fail("The product name for this locale is already taken.");
                    }
                }
            ],
            'description' => 'nullable|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'featured' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'category_ids.*.exists' => 'The category ID :input is invalid. Please select a valid category.',
        ];
    }
}
