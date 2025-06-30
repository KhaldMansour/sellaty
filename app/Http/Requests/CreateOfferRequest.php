<?php

namespace App\Http\Requests;

/**
 * @OA\Schema(
 *     schema="CreateOfferRequest",
 *     type="object",
 *     required={"text", "price"},
 *     @OA\Property(
 *         property="text",
 *         type="string",
 *         maxLength=1000,
 *         example="I would like to offer $150 for this item."
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         minimum=0,
 *         example=150.00
 *     )
 * )
 */
class CreateOfferRequest extends BaseFormRequest
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
            'text' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
        ];
    }
}
