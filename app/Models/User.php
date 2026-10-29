<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
