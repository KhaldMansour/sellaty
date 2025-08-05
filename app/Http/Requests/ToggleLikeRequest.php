<?php

namespace App\Http\Requests;

use Illuminate\Validation\Validator;

/**
 * @OA\Schema(
 *     schema="ToggleLikeRequestSchema",
 *     type="object",
 *     description="Toggle like for either a product or a user",
 *     oneOf={
 *         @OA\Schema(
 *             required={"product_id"},
 *             @OA\Property(
 *                 property="product_id",
 *                 type="integer",
 *                 example=12,
 *                 description="ID of the product to like or unlike"
 *             )
 *         ),
 *         @OA\Schema(
 *             required={"user_id"},
 *             @OA\Property(
 *                 property="user_id",
 *                 type="integer",
 *                 example=34,
 *                 description="ID of the user to like or unlike"
 *             )
 *         )
 *     }
 * )
 */
class ToggleLikeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $hasProduct = $this->filled('product_id');
            $hasUser = $this->filled('user_id');

            if (!($hasProduct xor $hasUser)) {
                $validator->errors()->add(
                    'likeable_target',
                    'You must provide either product_id or user_id, but not both.'
                );
            }
        });
    }
}
