<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
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
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'pixel_coin_balance' => 'integer',
        ];
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
