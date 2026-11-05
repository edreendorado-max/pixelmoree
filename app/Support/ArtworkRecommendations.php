<?php

namespace App\Support;

use App\Models\Artwork;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ArtworkRecommendations
{
    private const LIMIT = 8;

    public function forUser(Collection $artworks, ?User $user): Collection
    {
        $categoryInterests = [];
        $interactedArtworkIds = [];

        if ($user !== null) {
            $this->recordInterests(
                $user->likedArtworks()->get(['artworks.id', 'artworks.category']),
                3,
                $categoryInterests,
                $interactedArtworkIds,
            );

            $this->recordInterests(
                DB::table('pixel_coin_transactions')
                    ->join('artworks', 'artworks.id', '=', 'pixel_coin_transactions.artwork_id')
                    ->where('pixel_coin_transactions.user_id', $user->id)
                    ->where('pixel_coin_transactions.type', 'purchase')
                    ->get(['artworks.id', 'artworks.category']),
                4,
                $categoryInterests,
                $interactedArtworkIds,
            );

            $this->recordInterests(
                DB::table('artwork_views')
                    ->join('artworks', 'artworks.id', '=', 'artwork_views.artwork_id')
                    ->where('artwork_views.viewer_key', hash('sha256', 'user:' . $user->id))
                    ->get(['artworks.id', 'artworks.category']),
                1,
                $categoryInterests,
                $interactedArtworkIds,
            );
        }

        return $artworks
            ->reject(static fn (Artwork $artwork): bool => ($user !== null && (int) $artwork->user_id === (int) $user->id)
                || isset($interactedArtworkIds[$artwork->id]))
            ->sort(static function (Artwork $first, Artwork $second) use ($categoryInterests): int {
                $firstInterest = $categoryInterests[mb_strtolower(trim((string) $first->category))] ?? 0;
                $secondInterest = $categoryInterests[mb_strtolower(trim((string) $second->category))] ?? 0;

                return ($secondInterest <=> $firstInterest)
                    ?: (($second->likes_count * 3 + $second->views_count)
                        <=> ($first->likes_count * 3 + $first->views_count))
                    ?: (($second->created_at?->getTimestamp() ?? 0)
                        <=> ($first->created_at?->getTimestamp() ?? 0));
            })
            ->take(self::LIMIT)
            ->values();
    }

    /**
     * @param  array<string, int>  $categoryInterests
     * @param  array<string, bool>  $interactedArtworkIds
     */
    private function recordInterests(iterable $artworks, int $weight, array &$categoryInterests, array &$interactedArtworkIds): void
    {
        foreach ($artworks as $artwork) {
            $interactedArtworkIds[$artwork->id] = true;
            $category = mb_strtolower(trim((string) $artwork->category));

            if ($category !== '') {
                $categoryInterests[$category] = ($categoryInterests[$category] ?? 0) + $weight;
            }
        }
    }
}
