<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateCategoryRequestSchema",
 *     @OA\Property(property="name", type="string", description="The name of the category", maxLength=255),
 *     @OA\Property(property="description", type="string", description="A description of the category"),
 *     @OA\Property(property="image", type="string", format="binary", description="The category image (optional)")
 * )
 */
class UpdateCategoryRequest extends FormRequest
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
                    $categoryExists = Category::where('name->' . $locale, '=', $value)
                        ->where('id', '!=', $this->route('category'))
                        ->exists();

                    if ($categoryExists) {
                        $fail("The category name for this locale is already taken.");
                    }
                }
            ],
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
