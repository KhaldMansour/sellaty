<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['chatable_id', 'chatable_type', 'buyer_id', 'seller_id'];

    protected $appends = ['name', 'users' , 'product_image'];

    protected $with = ['seller', 'buyer', 'offers', 'chatable'];

    protected $hidden = ['chatable', 'seller', 'buyer', 'offers'];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function chatable()
    {
        return $this->morphTo()->constrain([
            Product::class => fn ($q) => $q->withTrashed()->with(['images','customFieldValues','categories','seller']),
            WantedProduct::class => fn ($q) => $q->withTrashed()->with(['images', 'buyer']),
        ]);
    }
    public function getNameAttribute()
    {
        return $this->chatable?->name;
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function getUsersAttribute()
    {
        return collect([$this->buyer, $this->seller])->filter();
    }

    public static function getChatsWithProductSummary(int $userId)
    {
        return self::with([
            'chatable',

                'buyer',
                'seller',
                'latestMessage',
            ])
            ->withCount([
                'messages as unseen_messages_count' => function ($query) use ($userId) {
                    $query->where('sender_id', '!=', $userId)
                        ->whereNull('seen_at');
                }
            ]);
    }

    public function getProductImageAttribute()
    {
        return $this->chatable->images->first()?->image_url;
    }

    public function latestOffer()
    {
        return $this->hasOne(Offer::class)->latestOfMany();
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }
}
