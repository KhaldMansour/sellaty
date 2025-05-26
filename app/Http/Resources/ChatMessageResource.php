<?php

namespace App\Http\Resources;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ChatMessageSchema",
 *     type="object",
 *     title="Chat Message",
 *     description="Chat message resource structure",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="chat_id", type="integer", example=5),
 *     @OA\Property(property="sender_id", type="integer", example=12),
 *     @OA\Property(property="sender_name", type="string", example="Easton Prohaska"),
 *     @OA\Property(property="content", type="string", example="Hello, how are you?"),
 *     @OA\Property(property="type", type="string", example="text"),
 *     @OA\Property(property="seen_at", type="string", format="date-time", nullable=true, example=null),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-03T22:45:43.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-03T22:45:43.000000Z")
 * )
 */
class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isText = $this->type === ChatMessage::$TYPE_TEXT;

        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'sender_id' => $this->sender_id,
            'sender_name' => $this->sender->first_name . ' ' . $this->sender->last_name,
            'content' => $isText ? $this->content : '',
            'type' => $this->type,
            'seen_at' => $this->seen_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
