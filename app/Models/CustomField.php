<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    public const TYPE_TEXT = 'text';
    public const TYPE_NUMBER = 'number';
    public const TYPE_DATE = 'date';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_SELECT = 'select';
    public const TYPE_YEAR = 'year';

    public const TYPES = [
        self::TYPE_TEXT,
        self::TYPE_NUMBER,
        self::TYPE_DATE,
        self::TYPE_BOOLEAN,
        self::TYPE_SELECT,
        self::TYPE_YEAR,
    ];

    protected $fillable = ['name', 'type', 'required'];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function options()
    {
        return $this->hasMany(CustomFieldOption::class);
    }
}
