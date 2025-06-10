<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="FilterProductRequest",
 *     type="object",
 *     @OA\Property(property="status", type="string", nullable=true, description="status"),
 *     @OA\Property(property="category", type="integer", nullable=true, description="category"),
 *     @OA\Property(property="condition", type="string", nullable=true, description="condition"),
 *     @OA\Property(property="min_price", type="number", nullable=true, description="min_price"),
 *     @OA\Property(property="max_price", type="number", nullable=true, description="max_price"),
 *     @OA\Property(property="price_order", type="string", nullable=true, description="price_order"),
 *     @OA\Property(property="creation_order", type="string", nullable=true, description="creation_order"),
 *     @OA\Property(property="longitude", type="number", nullable=true, description="longitude"),
 *     @OA\Property(property="latitude", type="number", nullable=true, description="latitude"),
 *     @OA\Property(property="radius", type="number", nullable=true, description="radius"),
 * )
 */
class FilterProductRequest extends FormRequest
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
        $statusTypes = implode(',', Product::getStatuses());

        return [
            'status' => 'nullable|string|in:' . $statusTypes,
            'category' => 'nullable|number|exists:categories,id',
            'condition' => 'nullable|string|in:New,Used',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'price_order' => 'nullable|string|in:asc,desc',
            'creation_order' => 'nullable|string|in:asc,desc',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'radius' => 'nullable|numeric|min:0',
        ];
    }
}
