<?php

namespace App\Http\Requests;

/**
 * @OA\Schema(
 *     schema="ListResourceRequestSchema",
 *     title="List Resource Request",
 *     description="Request parameters for listing resources with optional filters.",
 *     @OA\Property(property="limit", type="integer", example=10, description="The number of items to return per page. Maximum value is 100."),
 *     @OA\Property(property="search", type="string", example="", description="Search term to filter resources.", nullable=true),
 *     @OA\Property(property="searchFields", type="string", example="", description="Fields to search within. Can be 'name' or 'description'.", nullable=true),
 *     @OA\Property(property="find", type="string", example="", description="Additional filters as key-value pairs.", nullable=true),
 *     @OA\Property(property="locale", type="string", example="", description="The locale to apply for translations. Defaults to 'en'.", nullable=true)
 * )
 */

class ListResourceRequest extends BaseFormRequest
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
            'limit' => 'nullable|integer|max:100',
            'search' => 'nullable|string',
            'searchFields' => 'nullable|string',
            'find' => 'nullable|string',
            'page' => 'nullable|integer',
        ];
    }
}
