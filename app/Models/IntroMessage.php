<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Prettus\Repository\Traits\TransformableTrait;
use Spatie\Translatable\HasTranslations;

/**
 * Class IntroMessage.
 *
 * @package namespace App\Entities;
 */
class IntroMessage extends Model
{
    use TransformableTrait;

    use HasTranslations;

    public $translatable = ['text_message'];

    protected $fillable = [
        'image_url',
        'text_message',
        'active',
    ];

    protected $casts = [
        'text_message' => 'array',
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
            $imagePath = request()->file('image')->store('intro_screens__images', 'public');
            $imageUrl = asset('storage/' . $imagePath);
            $model->image_url = $imageUrl;

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
