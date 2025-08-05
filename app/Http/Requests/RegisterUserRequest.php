<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @OA\Schema(
 *     schema="RegisterUserRequestSchema",
 *     type="object",
 *     required={"first_name", "email", "password", "password_confirmation", "phone_number" , "username"},
 *
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="username", type="string", example="JohnDoe"),
 *
 *     @OA\Property(
 *         property="profile_photo",
 *         type="string",
 *         format="binary",
 *         description="Profile photo image file (jpeg, png, jpg, gif, svg)"
 *     ),
 *     @OA\Property(property="email", type="string", format="email", example="hello@gmail.com"),
 *
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         example="StrongP@ss1",
 *         description="Min 6 characters, at least one uppercase, one lowercase, and one special character"
 *     ),
 *
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         example="StrongP@ss1"
 *     ),
 *
 *     @OA\Property(
 *         property="phone_number",
 *         type="string",
 *         example="+201000000000",
 *         description="E.164 international format"
 *     )
 * )
 */

class RegisterUserRequest extends BaseFormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'string|max:255',
            'username' => 'required|string|max:255',
            'profile_photo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:12048',
            'email' => 'string|email|unique:users',
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{6,}$/',
            ],
            'phone_number' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/|unique:users',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errorString = implode(' ', $validator->errors()->all());

        throw new HttpException(422, $errorString);
    }
}
