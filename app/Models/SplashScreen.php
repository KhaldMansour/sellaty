<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SplashScreen extends Model
{
    protected $fillable = [
        'image_url',
        'display_time',
        'text_message',
        'active',
        'display_order',
    ];
}
