<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldOption extends Model
{
    protected $fillable = ['custom_field_id', 'value', 'parent_option_id' , 'image_url'];

    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }

    public function parent()
    {
        return $this->belongsTo(CustomFieldOption::class, 'parent_option_id');
    }

    public function children()
    {
        return $this->hasMany(CustomFieldOption::class, 'parent_option_id');
    }

    public function productCustomFieldValues()
    {
        return $this->hasMany(ProductCustomFieldValue::class, 'value');
    }
}
