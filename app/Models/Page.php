<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasTranslations;

    protected $fillable = ['title', 'slug', 'content', 'published'];

    public $translatable = ['content' , 'title'];

    public function getRouteKeyName()
    {
        return 'slug';
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
            $model->handleTranslations();
        });

        static::saved(function ($model) {
            $model->handleTranslations();
        });

        static::updating(function ($model) {
            $model->handleTranslations();
        });
    }
}
