<?php

namespace App\Http\Requests;

use App\Models\ChatMessage;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="SendChatMessageRequest",
 *     type="object",
 *     required={"text"},
 *     @OA\Property(
 *         property="text",
 *         type="string",
 *         maxLength=256,
 *         example="Hello, I'm interested in your product"
 *     )
 * )
 */
class SendChatMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $types = implode(',', ChatMessage::types());

        return [
            'type' => 'required|in:' . $types,
            'content' => 'required_if:type,text|string|nullable',
            'file' => 'required_if:type,image,voice|file|mimes:jpeg,png,jpg,mp3,wav,ogg|max:10240',
        ];
    }
}
