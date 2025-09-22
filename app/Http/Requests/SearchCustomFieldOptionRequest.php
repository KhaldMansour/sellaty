<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="SearchCustomFieldOptionRequest",
 *     type="object",
 *     required={"custom_field_id", "value"},
 *     @OA\Property(
 *         property="custom_field_id",
 *         type="integer",
 *         example=1,
 *         description="The ID of the custom field to search within."
 *     ),
 *     @OA\Property(
 *         property="value",
 *         type="string",
 *         example="Option",
 *         description="The value to search for within the custom field options."
 *     )
 * )
 */
class SearchCustomFieldOptionRequest extends FormRequest
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
            'custom_field_id' => ['required', 'integer', 'exists:custom_fields,id'],
            'value' => ['required', 'string'],
        ];
    }
}
