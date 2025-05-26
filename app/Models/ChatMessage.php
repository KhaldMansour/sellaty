<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    public static string $TYPE_TEXT = 'text';
    public static string $TYPE_VOICE = 'voice';
    public static string $TYPE_IMAGE = 'image';

    public static function types(): array
    {
        return [
            self::$TYPE_TEXT,
            self::$TYPE_VOICE,
            self::$TYPE_IMAGE,
        ];
    }

    protected $fillable = ['chat_id', 'sender_id', 'content', 'type', 'seen_at'];

    protected $appends = ['sender_name'];

    protected $with = ['sender'];

    protected $hidden = ['sender'];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isSeen()
    {
        return !is_null($this->seen_at);
    }

    public function getSenderNameAttribute()
    {
        return $this->sender?->full_name;
    }
}
