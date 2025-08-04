<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Filament\Models\Contracts\HasName;

class User extends Authenticatable implements JWTSubject, FilamentUser, HasName
{
    use HasFactory;
    use Notifiable;
    use Authorizable;
    use HasFactory;
    use HasApiTokens;

    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPER_ADMIN = 'super_admin';

    // Optionally, a list for easier mapping or validation
    public const ROLES = [
        self::ROLE_USER,
        self::ROLE_ADMIN,
        self::ROLE_SUPER_ADMIN,
    ];

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
        'fcm_token',
        'roles',
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
            'roles' => 'array',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->roles)) {
                $user->roles = [self::ROLE_USER];
            }
        });

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

    public function getFilamentName(): string
    {
        return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function hasRole(string|array $role): bool
    {
        if (is_array($role)) {
            return !empty(array_intersect($role, $this->roles ?? []));
        }

        return in_array($role, $this->roles ?? []);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
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
