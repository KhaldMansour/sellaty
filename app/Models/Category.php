<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'image_url',
        'slug'
    ];

    public $translatable = ['name' , 'description'];

    public $with = ['customFields'];

    public function customFields()
    {
        return $this->belongsToMany(CustomField::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }

    public function wantedProducts()
    {
        return $this->belongsToMany(WantedProduct::class, 'category_wanted_product');
    }

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
            // $imagePath = request()->file('image')->store('categories', 'public');
            // $imageUrl = asset('storage/' . $imagePath);
            // $model->image_url = $imageUrl;

            $englishName = $model->getTranslation('name', 'en');

            $model->handleTranslations();

            if ($englishName) {
                $slug = Str::of($englishName)
                            ->lower()
                            ->replaceMatches('/[^a-z0-9]+/', '_')
                            ->trim('_');
                $model->slug = $slug;
            } else {
                $model->slug = Str::random(10);
            }
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
