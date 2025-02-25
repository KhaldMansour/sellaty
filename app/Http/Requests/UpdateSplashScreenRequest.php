<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateSplashScreenRequest",
 *     type="object",
 *     @OA\Property(property="image", type="string", format="binary", nullable=true, description="Splash screen image (jpeg, png, jpg, gif, svg), max size 2MB"),
 *     @OA\Property(property="display_time", type="integer", nullable=true, description="The display time in seconds for the splash screen"),
 *     @OA\Property(property="text_message", type="string", maxLength=255, nullable=true, description="Text message to display on the splash screen"),
 *     @OA\Property(property="active", type="boolean", nullable=true, description="Indicates if the splash screen is active or not"),
 *     @OA\Property(property="display_order", type="integer", nullable=true, description="The order in which the splash screen is displayed relative to others")
 * )
 */

class UpdateSplashScreenRequest extends FormRequest
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'display_time' => 'nullable|integer',
            'text_message' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
        ];
    }
}
