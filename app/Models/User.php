<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory;
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'cash_balance_minor',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'cash_balance_minor' => 'integer',
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

    public function likedArtworks(): BelongsToMany
    {
        $userId = $this->getKey();

        return $this->belongsToMany(Artwork::class, 'artwork_likes')
            ->withTimestamps()
            ->where(static fn ($query) => $query->whereNull('artworks.user_id')
                ->orWhere('artworks.user_id', '<>', $userId));
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
