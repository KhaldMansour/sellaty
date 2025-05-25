<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;
    use Authorizable;
    use HasFactory;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'profile_photo',
        'phone_number',
        'location',
        'is_verified'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted()
    {
        static::updating(function ($user) {
            if (request()->hasFile('profile_photo')) {
                if ($user->getOriginal('profile_photo')) {
                    $oldPath = str_replace(asset('storage/'), '', $user->getOriginal('profile_photo'));
                    Storage::disk('public')->delete($oldPath);
                }
    
                $imagePath = request()->file('profile_photo')->store('users', 'public');
                $imageUrl = asset('storage/' . $imagePath);
                $user->profile_photo = $imageUrl;
            }
        });

        static::saved(function ($model) {
            if (request()->has('profile_photo')) {
                $imagePath = request()->file('profile_photo')->store('users', 'public');
                $imageUrl = asset('storage/' . $imagePath);
                $model->profile_photo = $imageUrl;
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean'
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function getFullNameAttribute()
    {
        return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function wantedProducts()
    {
        return $this->hasMany(WantedProduct::class);
    }

    public function buyerChats()
    {
        return $this->hasMany(Chat::class, 'buyer_id');
    }

    public function sellerChats()
    {
        return $this->hasMany(Chat::class, 'seller_id');
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }
}
