<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StorePageRequest",
 *     title="Store Page Request",
 *     description="Request schema for creating a new static page.",
 *     required={"title", "slug", "content"},
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         maxLength=255,
 *         example="About Us",
 *         description="The title of the page"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         example="about-us",
 *         description="Unique slug for the page URL"
 *     ),
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         example="<p>This is the about us page content.</p>",
 *         description="HTML content of the page"
 *     ),
 *     @OA\Property(
 *         property="published",
 *         type="boolean",
 *         example=true,
 *         description="Whether the page is published"
 *     )
 * )
 */
class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:pages,slug',
            'content' => 'required|string',
            'published' => 'boolean',
        ];
    }
}
