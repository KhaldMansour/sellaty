<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateUserLocaleRequest",
 *     type="object",
 *     title="Update User Locale Request",
 *     description="Request body to update the user's locale.",
 *     required={"locale"},
 *     @OA\Property(
 *         property="locale",
 *         type="string",
 *         example="en",
 *         description="User's preferred language. Must be one of the supported locales: en, fr, ar, de, es."
 *     )
 * )
 */
class UpdateUserLocaleRequest extends FormRequest
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
            'locale' => ['required', 'string', 'in:' . implode(',', config('app.supported_locales'))],
        ];
    }
}
