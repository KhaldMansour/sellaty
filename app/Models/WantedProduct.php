<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WantedProduct extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PENDING = 'pending';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_REJECTED,
            self::STATUS_PENDING
        ];
    }

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
        'status',
        'deleted_at'
    ];

    protected $casts = [
        'condition' => 'array',
        'delivery_options' => 'array',
        'listed_until' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $with = ['images', 'buyer'];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'city' => ''
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
            $model->setListedUntil();
        });

        static::saved(function ($model) {
        });

        static::updating(function ($model) {
            if ($model->isDirty('duration')) {
                $model->setListedUntil();
            }
        });

        static::deleting(function ($model) {
        });
    }

    public function setListedUntil()
    {
        $this->attributes['listed_until'] = $this->calculateExpirationDate($this)->toDateString();
    }

    public function calculateExpirationDate($wantedProduct)
    {
        $duration = strtolower($wantedProduct->duration);
        $pattern = '/(\d+)\s*(week|weeks|day|days|month|months)/';


        if (preg_match($pattern, $duration, $matches)) {
            $amount = (int) $matches[1];
            $unit = $matches[2];

            if (in_array($unit, ['week', 'weeks'])) {
                return Carbon::now()->addWeeks($amount);
            } elseif (in_array($unit, ['day', 'days'])) {
                return Carbon::now()->addDays($amount);
            } elseif (in_array($unit, ['month', 'months'])) {
                return Carbon::now()->addMonths($amount);
            }
        }

        return Carbon::now();
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'user_id');
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
