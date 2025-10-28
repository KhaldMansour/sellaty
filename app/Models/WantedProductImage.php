<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WantedProductImage extends Model
{
    protected $table = 'wanted_product_images';

    protected $fillable = [
        'wanted_product_id',
        'image_url',
        'thumbnail_path',
    ];

    public function wantedProduct()
    {
        return $this->belongsTo(WantedProduct::class);
    }
}
