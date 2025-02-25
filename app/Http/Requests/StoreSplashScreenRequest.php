<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreSplashScreenRequest",
 *     type="object",
 *     @OA\Property(property="image", type="string", format="binary", description="Splash screen image"),
 *     @OA\Property(property="display_time", type="integer", nullable=true, description="Display time for the splash screen"),
 *     @OA\Property(property="text_message", type="string", description="Text message for the splash screen"),
 *     @OA\Property(property="active", type="integer", description="Whether the splash screen is active"),
 *     @OA\Property(property="display_order", type="integer", nullable=true, description="Order to display the splash screen")
 * )
 */
class StoreSplashScreenRequest extends FormRequest
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
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image validation
            'display_time' => 'nullable|integer',
            'text_message' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
        ];
    }
}
