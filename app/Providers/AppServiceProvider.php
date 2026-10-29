<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Artwork;
use App\Support\SitePageContent;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Auth;
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
            $view->with('homeContent', SitePageContent::get('home'));
            $view->with('aboutContent', SitePageContent::get('about'));
            $view->with('homeArtworks', Artwork::query()
                ->where('status', 'Listed')
                ->with('user')
                ->latest()
                ->get());
            $view->with('topCreators', User::query()
                ->whereHas('artworks', static fn ($query) => $query->where('status', 'Listed'))
                ->withCount(['artworks' => static fn ($query) => $query->where('status', 'Listed')])
                ->orderByDesc('artworks_count')
                ->orderBy('name')
                ->limit(12)
                ->get());
            $view->with('teamCreators', User::query()
                ->whereNotNull('avatar_path')
                ->withCount('artworks')
                ->orderBy('name')
                ->get());
            $view->with('userArtworks', Auth::user()?->artworks()->latest()->get() ?? collect());
        });

        View::composer('week12.client.index', static function (ViewContract $view): void {
            $view->with('marketplaceContent', SitePageContent::get('marketplace'));
        });
    }
}
