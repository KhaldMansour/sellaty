<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['product_id', 'buyer_id', 'seller_id'];

    protected $appends = ['name', 'users'];

    protected $with = ['seller', 'buyer'];

    protected $hidden = ['product'];

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

    public function getUsersAttribute()
    {
        return collect([$this->buyer, $this->seller])->filter();
    }
}
