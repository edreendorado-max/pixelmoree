<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasFactory;
    use MustVerifyEmailTrait;
    use Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'bio',
        'avatar_path',
        'banner_path',
        'google_id',
        'facebook_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'pixel_coin_balance',
        'gcash_qr_path',
        'maya_qr_path',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'pixel_coin_balance' => 'integer',
            'cash_balance_minor' => 'integer',
            'cash_reserved_minor' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(static function (self $user): void {
            $user->account_id ??= (string) Str::uuid();
        });

        static::saved(static function (self $user): void {
            if (! $user->wasRecentlyCreated && ! $user->wasChanged(['phone', 'bio', 'avatar_path', 'banner_path'])) {
                return;
            }
            $profile = [
                'phone' => $user->phone,
                'bio' => $user->bio,
                'avatar_path' => $user->avatar_path,
                'banner_path' => $user->banner_path,
                'updated_at' => now(),
            ];
            $query = DB::table('user_profiles')->where('user_id', $user->id);
            if ($query->exists()) {
                $query->update($profile);
            } else {
                DB::table('user_profiles')->insert($profile + ['user_id' => $user->id, 'created_at' => now()]);
            }
        });
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class);
    }

    public function pixelCoinTransactions(): HasMany
    {
        return $this->hasMany(PixelCoinTransaction::class);
    }

    public function paymentOrders(): HasMany
    {
        return $this->hasMany(PaymentOrder::class, 'buyer_id');
    }

    public function artworkOwnerships(): HasMany
    {
        return $this->hasMany(ArtworkOwnership::class, 'owner_id');
    }

    public function profileViews(): HasMany
    {
        return $this->hasMany(CreatorView::class, 'creator_id');
    }

    public function profileLikes(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'creator_likes', 'creator_id', 'user_id')->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'creator_follows', 'creator_id', 'user_id')->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'creator_follows', 'user_id', 'creator_id')->withTimestamps();
    }

    public function likedArtworks(): BelongsToMany
    {
        return $this->belongsToMany(Artwork::class, 'artwork_likes')->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
