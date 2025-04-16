<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslations;

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
        'active',
        'negotiable',
        'deliverable',
        'user_id',
        'featured',
        'currency'
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'condition' => 'array',
        'delivery_options' => 'array',
        'active' => 'boolean',
        'negotiable' => 'boolean',
        'deliverable' => 'boolean',
        'featured' => 'boolean',
        'listed_until' => 'date',
        'price' => 'decimal:2',
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
            $model->active = $model->active ?? 1;
            $model->handleTranslations();
        });

        static::saved(function ($model) {
            if (request()->has('images')) {
                $productImages = request()->images;
                foreach ($productImages as $image) {
                    $imagePath = $image->store('products', 'public');
                    $imageUrl = asset('storage/' . $imagePath);

                    if (!Storage::disk('public')->exists($imagePath)) {
                        Log::error('File storage failed', ['path' => $imagePath]);
                    }

                    $model->images()->create([
                        'image_url' => $imageUrl,
                        'product_id' => $model->id,
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
}
