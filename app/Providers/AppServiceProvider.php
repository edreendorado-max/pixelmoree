<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Artwork;
use App\Support\ArtworkPriceDashboard;
use App\Support\ArtworkRecommendations;
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
            $dailyPriceMovers = (new ArtworkPriceDashboard())->dailyMovers($homeArtworks);

            $view->with('homeContent', SitePageContent::get('home'));
            $view->with('aboutContent', SitePageContent::get('about'));
            $view->with('homeArtworks', $homeArtworks);
            $view->with('dailyPriceGainers', $dailyPriceMovers['gainers']);
            $view->with('dailyPriceDecliners', $dailyPriceMovers['decliners']);
            $view->with('priceComparisonDate', $dailyPriceMovers['comparison_date']);
            $view->with('forYouArtworks', (new ArtworkRecommendations())->forUser($homeArtworks, $account));
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
            $view->with('purchaseOrders', $account?->paymentOrders()
                ->where('type', 'nft_purchase')
                ->with('artwork')
                ->latest()
                ->limit(50)
                ->get() ?? collect());
            $ownedArtworks = collect();
            if ($account !== null) {
                $latestListings = DB::table('resale_listings')
                    ->selectRaw('MAX(id) AS id, artwork_id')
                    ->where('seller_id', $account->id)
                    ->groupBy('artwork_id');
                $ownedArtworks = DB::table('artwork_ownerships')
                    ->join('artworks', 'artworks.id', '=', 'artwork_ownerships.artwork_id')
                    ->leftJoinSub($latestListings, 'latest_listing', static fn ($join) => $join
                        ->on('latest_listing.artwork_id', '=', 'artworks.id'))
                    ->leftJoin('resale_listings as listing', 'listing.id', '=', 'latest_listing.id')
                    ->where('artwork_ownerships.owner_id', $account->id)
                    ->select([
                        'artworks.id', 'artworks.product_id', 'artworks.title', 'artworks.image',
                        'listing.status as resale_status',
                    ])
                    ->orderBy('artworks.title')
                    ->get();
            }
            $view->with('ownedArtworks', $ownedArtworks);
        });

        View::composer('user.pixel-coin', static function (ViewContract $view): void {
            $account = Auth::user();
            $view->with('account', $account);
            $view->with('cashOrders', $account?->paymentOrders()->latest()->limit(20)->get() ?? collect());
            $view->with('cashLedger', $account === null
                ? collect()
                : DB::table('cash_ledger_entries')->where('user_id', $account->id)->latest()->limit(20)->get());
            $view->with('usdRate', (float) config('payments.php_per_usd', 58));
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
