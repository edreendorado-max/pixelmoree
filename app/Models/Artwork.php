<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Support\ArtworkMarketValue;

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

        return ArtworkMarketValue::estimate(
            (float) $this->price,
            $this->created_at,
            (int) $recentSales,
            $lastSaleAt,
        );
    }

    protected function creator(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $this->user?->name ?? $value,
        );
    }
}
