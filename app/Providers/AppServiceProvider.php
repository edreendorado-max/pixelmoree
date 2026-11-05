<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Artwork;
use App\Support\SitePageContent;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-admin', static fn (User $user): bool => $user->isAdmin());

        View::composer(['home', 'about'], static function (ViewContract $view): void {
            $account = Auth::user();
            $account?->loadCount(['profileViews', 'profileLikes', 'followers']);
            $homeArtworks = Artwork::query()
                ->where('status', 'Listed')
                ->with(['user' => static fn ($query) => $query->withCount(['followers', 'profileViews'])])
                ->withCount(['likes', 'views'])
                ->withCount(['pixelCoinTransactions as recent_market_sales_count' => static fn ($query) => $query
                    ->where('type', 'purchase')
                    ->where('created_at', '>=', now()->subDays(30))])
                ->withMax(['pixelCoinTransactions as last_market_sale_at' => static fn ($query) => $query
                    ->where('type', 'purchase')], 'created_at')
                ->latest()
                ->get();
            $creatorSales = DB::table('pixel_coin_transactions as purchases')
                ->join('artworks as sold_artworks', 'sold_artworks.id', '=', 'purchases.artwork_id')
                ->where('purchases.type', 'purchase')
                ->whereNotNull('sold_artworks.user_id')
                ->groupBy('sold_artworks.user_id')
                ->selectRaw('sold_artworks.user_id, COUNT(*) AS sold_count')
                ->pluck('sold_count', 'user_id');
            foreach ($homeArtworks as $artwork) {
                $artwork->setAttribute('creator_sold_count', (int) ($creatorSales[$artwork->user_id] ?? 0));
            }

            $view->with('homeContent', SitePageContent::get('home'));
            $view->with('aboutContent', SitePageContent::get('about'));
            $view->with('homeArtworks', $homeArtworks);
            $view->with('forYouArtworks', $homeArtworks
                ->sortByDesc(static fn (Artwork $artwork): int => (int) $artwork->likes_count * 3 + (int) $artwork->views_count)
                ->take(4)
                ->values());
            $view->with('topCreators', User::query()
                ->where(static fn ($query) => $query->where('role', '<>', 'admin')->orWhereNull('role'))
                ->whereHas('artworks', static fn ($query) => $query->where('status', 'Listed'))
                ->withCount([
                    'artworks' => static fn ($query) => $query->where('status', 'Listed'),
                    'followers',
                    'profileLikes',
                    'profileViews',
                ])
                ->orderByDesc('artworks_count')
                ->orderBy('name')
                ->limit(12)
                ->get());
            $view->with('likedArtworkIds', $account?->likedArtworks()->pluck('artworks.id')->all() ?? []);
            $view->with('userArtworks', $account?->artworks()->latest()->get() ?? collect());
        });

        View::composer('login', static function (ViewContract $view): void {
            $view->with('authContent', SitePageContent::get('login'));
        });

        View::composer('register', static function (ViewContract $view): void {
            $view->with('authContent', SitePageContent::get('register'));
        });

        View::composer(['week12.client.index', 'week12.client.show'], static function (ViewContract $view): void {
            $view->with('marketplaceContent', SitePageContent::get('marketplace'));
        });
    }
}
