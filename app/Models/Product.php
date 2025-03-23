<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'price',
        'description',
        'category_id',
        'quantity',
        'featured',
        'user_id'
    ];

    public $translatable = ['name' , 'description'];

    protected $hidden = ['pivot'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    protected $casts = [
        'name' => 'array',
    ];

    protected function handleTranslations()
    {
        $locale = app()->getLocale();
        foreach ($this->translatable as $field) {
            if (isset($this->$field)) {
                $this->setTranslation($field, $locale, $this->$field);
            }
        }
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->handleTranslations();
        });

        static::saved(function ($model) {
            if (request()->has('images')) {
                $productImages = request()->images;
                foreach ($productImages as $image) {
                    // Store the image
                    $imagePath = $image->store('products', 'public');
                    $imageUrl = asset('storage/' . $imagePath);

                    // Save the image with the correct product_id
                    $model->images()->create([
                        'image_url' => $imageUrl,
                        'product_id' => $model->id, // Now the product_id is set after the model is saved
                    ]);
                }
            }

            $model->handleTranslations();
        });

        static::updating(function ($model) {
            $model->handleTranslations();
        });

        static::deleting(function ($model) {
            if ($model->image_url) {
                $imagePath = str_replace([url('/storage/'), 'storage/'], '', $model->image_url);
                Storage::disk('public')->delete($imagePath);
            }
        });
    }
}
