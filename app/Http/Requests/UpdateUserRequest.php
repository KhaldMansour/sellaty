<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @OA\Schema(
 *     schema="UpdateUserRequestSchema",
 *     type="object",
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(
 *         property="profile_photo",
 *         type="string",
 *         format="binary",
 *         description="Profile photo image file (jpeg, png, gif, etc.)"
 *     ),
 *     @OA\Property(property="location", type="string", example="Cairo, Egypt", nullable=true),
 *     @OA\Property(property="email", type="string", format="email", example="hello@gmail.com"),
 *     @OA\Property(property="password", type="string", format="password", example="P@ssw0rd"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="P@ssw0rd"),
 *     @OA\Property(
 *         property="phone_number",
 *         type="string",
 *         example="+201000000000",
 *         description="E.164 international format"
 *     )
 * )
 */
class UpdateUserRequest extends FormRequest
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
        $user = auth()->user();

        return [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'profile_photo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:12048',
            'location' => 'nullable|string|max:255',
            'email' => 'unique:users,email,' . $user->id,
            'password' => [
                'string',
                'min:6',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{6,}$/',
            ],
            'phone_number' => 'string|regex:/^\+?[1-9]\d{1,14}$/|unique:users,phone_number,' . $user->id,
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errorString = implode(' ', $validator->errors()->all());

        throw new HttpException(422, $errorString);
    }
}
