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
        'is_verified',
        'username',
        'locked',
        'fcm_token'
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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'locked' => 'boolean',
        ];
    }

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

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function likedUsers()
    {
        return $this->morphedByMany(User::class, 'likeable', 'likes');
    }

    public function likedProducts()
    {
        return $this->morphedByMany(Product::class, 'likeable', 'likes');
    }

    public function followings()
    {
        return $this->morphToMany(
            User::class,
            'likeable',
            'likes',
            'user_id',
            'likeable_id'
        )->wherePivot('likeable_type', self::class);
    }

    public function followers()
    {
        return $this->morphToMany(
            User::class,
            'likeable',
            'likes',
            'likeable_id',
            'user_id'
        )->wherePivot('likeable_type', self::class);
    }

    public function like($likeable)
    {
        return $this->likes()->firstOrCreate([
            'likeable_id' => $likeable->id,
            'likeable_type' => get_class($likeable),
        ]);
    }

    public function unlike($likeable)
    {
        return $this->likes()
            ->where('likeable_id', $likeable->id)
            ->where('likeable_type', get_class($likeable))
            ->delete();
    }

    public function hasLiked($likeable)
    {
        return $this->likes()
            ->where('likeable_id', $likeable->id)
            ->where('likeable_type', get_class($likeable))
            ->exists();
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
