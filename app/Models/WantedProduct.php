<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class WantedProduct extends Model
{
    use HasTranslations;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'description',
        'brand',
        'model',
        'duration',
        'min_price',
        'max_price',
        'user_id',
        'condition',
        'delivery_options',
        'address',
        'country',
        'state',
        'city',
        'postal_code',
        'listed_until',
        'currency',
        'longitude',
        'latitude',
    ];

    public $translatable = ['name' , 'description'];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'condition' => 'array',
        'delivery_options' => 'array',
        'listed_until' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
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
            $model->setListedUntilAttribute();
        });

        static::saved(function ($model) {
            if (request()->has('images')) {
                $productImages = request()->images;
                foreach ($productImages as $image) {
                    $imagePath = $image->store('wanted_products', 'public');
                    $imageUrl = asset('storage/' . $imagePath);

                    $model->images()->create([
                        'image_url' => $imageUrl,
                        'wanted_product_id' => $model->id,
                    ]);
                }
            };
            $model->handleTranslations();
        });

        static::updating(function ($model) {
            $model->handleTranslations();
        });

        static::deleting(function ($model) {
        });
    }

    public function setListedUntilAttribute()
    {
        $this->attributes['listed_until'] = $this->calculateExpirationDate($this)->toDateString();
    }

    public function calculateExpirationDate($wantedProduct)
    {
        $duration = strtolower($wantedProduct->duration);
        $pattern = '/(\d+)\s*(week|weeks|day|days)/';


        if (preg_match($pattern, $duration, $matches)) {
            $amount = (int) $matches[1];
            $unit = $matches[2];


            if (in_array($unit, ['week', 'weeks'])) {
                return Carbon::now()->addWeeks($amount);
            } elseif (in_array($unit, ['day', 'days'])) {
                return Carbon::now()->addDays($amount);
            }
        }

        return Carbon::now();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(WantedProductImage::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_wanted_product');
    }
}
