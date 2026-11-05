<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Support\ArtworkMarketValue;
use Illuminate\Support\Facades\DB;

class Artwork extends Model
{
    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'creator',
        'category',
        'price',
        'item_count',
        'image',
        'image_hash',
        'description',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pixelCoinTransactions(): HasMany
    {
        return $this->hasMany(PixelCoinTransaction::class, 'artwork_id');
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'artwork_likes')->withTimestamps();
    }

    public function views(): HasMany
    {
        return $this->hasMany(ArtworkView::class);
    }

    public function marketEstimate(): array
    {
        $recentSales = $this->getAttribute('recent_market_sales_count');
        $lastSaleAt = $this->getAttribute('last_market_sale_at');

        if (! array_key_exists('recent_market_sales_count', $this->attributes)
            || ! array_key_exists('last_market_sale_at', $this->attributes)) {
            $sales = $this->pixelCoinTransactions()->where('type', 'purchase');
            $recentSales = (clone $sales)->where('created_at', '>=', now()->subDays(30))->count();
            $lastSaleAt = (clone $sales)->max('created_at');
        }

        $likes = $this->attributes['likes_count'] ?? $this->likes()->count();
        $views = $this->attributes['views_count'] ?? $this->views()->count();
        $creator = $this->user;
        $followers = $creator?->getAttribute('followers_count') ?? $creator?->followers()->count() ?? 0;
        $creatorViews = $creator?->getAttribute('profile_views_count') ?? $creator?->profileViews()->count() ?? 0;
        $creatorSales = $this->attributes['creator_sold_count'] ?? ($creator === null
            ? 0
            : (int) DB::table('pixel_coin_transactions as purchases')
                ->join('artworks as sold_artworks', 'sold_artworks.id', '=', 'purchases.artwork_id')
                ->where('purchases.type', 'purchase')
                ->where('sold_artworks.user_id', $creator->id)
                ->count());

        return ArtworkMarketValue::estimate(
            (float) $this->price,
            $this->created_at,
            (int) $recentSales,
            $lastSaleAt,
            null,
            (int) $likes,
            (int) $views,
            (int) $followers,
            (int) $creatorViews,
            (int) $creatorSales,
            (int) ($this->item_count ?? 1),
        );
    }

    protected function creator(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $this->user?->name ?? $value,
        );
    }
}
