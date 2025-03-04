<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreIntroMessageRequest",
 *     type="object",
 *     @OA\Property(property="image", type="string", format="binary", description="Splash screen image"),
 *     @OA\Property(property="text_message", type="string", description="Text message for the splash screen"),
 *     @OA\Property(property="active", type="integer", description="Whether the splash screen is active"),
 * )
 */
class StoreIntroMessageRequest extends FormRequest
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'text_message' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
        ];
    }
}
