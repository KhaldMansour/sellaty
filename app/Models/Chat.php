<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['product_id', 'buyer_id', 'seller_id'];

    protected $appends = ['name', 'users' , 'product_image'];

    protected $with = ['seller', 'buyer' , 'offers' , 'product'];

    protected $hidden = ['product' , 'seller', 'buyer' , 'offers'];

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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getNameAttribute()
    {
        return $this->product?->name;
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
                'product' => function ($query) {
                    $query->select('id', 'name')
                        ->with([
                            'images' => function ($q) {
                                $q->limit(1);
                            },
                        ]);
                },
                'buyer',
                'seller',
                'latestOffer'
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
        return $this->product->images->first()?->image_url;
    }

    public function latestOffer()
    {
        return $this->hasOne(Offer::class)->latestOfMany();
    }
}
