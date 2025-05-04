<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['chat_id', 'sender_id', 'text', 'seen_at'];

    protected $appends = ['sender_name'];

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
        return $this->sender?->name;
    }
}
