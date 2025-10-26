<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image_url', 'scanned' , 'is_nsfw', 'thumbnail_path'];

    protected $attributes = [
        'scanned' => false,
        'is_nsfw' => false
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
