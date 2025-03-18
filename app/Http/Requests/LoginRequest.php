<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="LoginRequestSchema",
 *     type="object",
 *     @OA\Property(property="phone_number", type="string", example="+201005594752"),
 *     @OA\Property(property="otp", type="string", example="800085")
 * )
 */

class LoginRequest extends FormRequest
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
            'phone_number' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/|exists:users,phone_number',
            'otp' => 'required|numeric|digits:6',
        ];
    }
}
