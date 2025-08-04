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
            $englishName = $model->getTranslation('name', 'en');

            if ($englishName) {
                $baseSlug = Str::of($englishName)
                                ->lower()
                                ->replaceMatches('/[^a-z0-9]+/', '_')
                                ->trim('_')
                                ->toString();

                $slug = $baseSlug;
                $counter = 1;

                $counter = 1;
                $maxAttempts = 10;

                while (self::where('slug', $slug)->exists()) {
                    if ($counter > $maxAttempts) {
                        $slug = $baseSlug . '_' . Str::random(5);
                        break;
                    }

                    $slug = $baseSlug . '_' . $counter++;
                }

                $model->slug = $slug;
            } else {
                $model->slug = Str::random(10);
            }
        });

        static::deleting(function ($model) {
            if ($model->image_url) {
                Storage::disk('public')->delete($model->image_url);
            }
        });
    }
}
