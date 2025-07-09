<?php

namespace App\Models;

use App\Jobs\ProcessProductImages;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslations;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
        ];
    }

    protected $fillable = [
        'name',
        'description',
        'brand',
        'model',
        'price',
        'duration',
        'quantity',
        'condition',
        'delivery_options',
        'address',
        'country',
        'state',
        'city',
        'postal_code',
        'listed_until',
        'negotiable',
        'deliverable',
        'user_id',
        'featured',
        'currency',
        'status',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'condition' => 'array',
        'delivery_options' => 'array',
        'negotiable' => 'boolean',
        'deliverable' => 'boolean',
        'featured' => 'boolean',
        'listed_until' => 'date',
        'price' => 'decimal:2',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public $translatable = ['name' , 'description'];

    protected $hidden = ['pivot'];

    protected $with = ['images' , 'customFieldValues' , 'categories' , 'seller'];


    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }

    public function customFieldValues()
    {
        return $this->hasMany(ProductCustomFieldValue::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
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
            $model->setListedUntilAttribute();
            $model->handleTranslations();
        });

        static::saved(function ($model) {
            if (request()->has('images')) {
                $productImages = request()->images;
                foreach ($productImages as $image) {
                    $tempPath = $image->store('products', 'public');

                    ProcessProductImages::dispatch($tempPath, $model);
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

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function likedByUsers()
    {
        return $this->morphToMany(User::class, 'likeable', 'likes');
    }

    public function scopeWithinRadius($query, $lat, $lng, $radius = 10)
    {
        return $query->select('*')
            ->selectRaw("(
                6371 * acos(
                    cos(radians(?)) *
                    cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(latitude))
                )
            ) AS distance", [$lat, $lng, $lat])
            ->whereRaw("(
                6371 * acos(
                    cos(radians(?)) *
                    cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(latitude))
                )
            ) <= ?", [$lat, $lng, $lat, $radius])
            ->orderBy('distance');
    }
}
