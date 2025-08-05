<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = ['text' , 'price' , 'product_id' , 'user_id' , 'status' , 'chat_id'];
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->status = self::STATUS_PENDING;
        });
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
            self::STATUS_REJECTED,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
