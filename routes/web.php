<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Support\SitePageContent;
use App\Models\Artwork;

require_once base_path('app/Week12Helpers.php');
require_once base_path('app/ArtworkRepository.php');

$authController = \App\Http\Controllers\AuthController::class;
$passwordResetController = \App\Http\Controllers\PasswordResetController::class;
$socialAuthController = \App\Http\Controllers\SocialAuthController::class;
$creatorController = \App\Http\Controllers\CreatorController::class;
$engagementController = \App\Http\Controllers\EngagementController::class;
$artworkController = \App\Http\Controllers\ArtworkController::class;
$earningsController = \App\Http\Controllers\UserEarningsController::class;
$profileController = \App\Http\Controllers\UserProfileController::class;
$pixelCoinController = \App\Http\Controllers\PixelCoinController::class;
$paymentOrderController = \App\Http\Controllers\PaymentOrderController::class;
$resaleListingController = \App\Http\Controllers\ResaleListingController::class;
$sitePageContentController = \App\Http\Controllers\SitePageContentController::class;
$adminController = \App\Http\Controllers\AdminController::class;
$week12ArtworkStore = static fn (): ArtworkRepository => new ArtworkRepository(DB::connection()->getPdo());

Route::get('/', static fn () => auth()->check()
    ? redirect()->route('user')
    : view('home'))->name('home');
Route::redirect('/week12', '/store')->name('week12.client');
Route::view('/about', 'about')->name('about');
Route::get('/nft/{id}', static fn (string $id) => auth()->check()
    ? redirect()->route('user.nft.show', ['id' => $id])
    : view('home'))->where('id', '[A-Za-z0-9][A-Za-z0-9-]*')->name('nft.guest');
Route::view('/login', 'login')->name('login');
Route::get('/register', [$authController, 'create'])->name('register');
Route::get('/create-account', [$authController, 'create'])->name('create-account');
Route::get('/forgot-password', [$passwordResetController, 'createLink'])->name('password.request');
Route::post('/forgot-password', [$passwordResetController, 'sendLink'])->middleware('throttle:5,1')->name('password.email');
Route::get('/reset-password/{token}', [$passwordResetController, 'createForm'])->name('password.reset');
Route::post('/reset-password', [$passwordResetController, 'reset'])->middleware('throttle:5,1')->name('password.update');
Route::post('/login', [$authController, 'login'])->middleware('throttle:10,1')->name('login.store');
Route::post('/register', [$authController, 'store'])->name('register.store');
Route::get('/email/verify', [$authController, 'verificationNotice'])->name('verification.notice');
Route::post('/email/verify', [$authController, 'verifyEmail'])->middleware('throttle:10,1')->name('verification.verify');
Route::post('/email/verification/resend', [$authController, 'resendVerification'])->middleware('throttle:3,1')->name('verification.resend');
Route::middleware(['guest', 'throttle:10,1'])->group(function () use ($socialAuthController): void {
    Route::get('/auth/{provider}/redirect', [$socialAuthController, 'redirect'])
        ->whereIn('provider', ['google', 'facebook'])
        ->name('social.redirect');
    Route::get('/auth/{provider}/callback', [$socialAuthController, 'callback'])
        ->whereIn('provider', ['google', 'facebook'])
        ->name('social.callback');
});
Route::post('/logout', [$authController, 'logout'])->middleware('auth')->name('logout');

Route::get('/creators/{creator}', [$creatorController, 'show'])
    ->whereNumber('creator')
    ->name('creators.show');

Route::middleware('throttle:120,1')->group(function () use ($engagementController): void {
    Route::get('/api/artworks/{artwork}/engagement', [$engagementController, 'showArtwork'])
        ->name('api.artworks.engagement');
    Route::post('/api/artworks/{artwork}/views', [$engagementController, 'viewArtwork'])
        ->name('api.artworks.views');
    Route::get('/api/creators/{creator}/engagement', [$engagementController, 'showCreator'])
        ->whereNumber('creator')
        ->name('api.creators.engagement');
    Route::post('/api/creators/{creator}/views', [$engagementController, 'viewCreator'])
        ->whereNumber('creator')
        ->name('api.creators.views');
});

Route::middleware('auth')->group(function () use ($profileController, $artworkController, $pixelCoinController, $earningsController, $engagementController, $paymentOrderController, $resaleListingController): void {
    Route::get('/store', static fn () => view('store', [
        'artworks' => Artwork::query()
            ->where('status', 'Listed')
            ->with('user')
            ->withCount(['likes', 'views'])
            ->latest()
            ->get(),
    ]))->name('store');

    Route::view('/user', 'home')->name('user');
    Route::view('/user/me', 'home')->name('user.me');
    Route::view('/user/dashboard', 'home')->name('user.dashboard');
    Route::view('/user/create', 'home')->name('user.create');
    Route::view('/user/notifications', 'home')->name('user.notifications');
    Route::view('/user/about', 'home')->name('user.about');
    Route::view('/user/purchases', 'home')->name('user.purchases');
    Route::get('/user/nft-management', [$artworkController, 'index'])->name('user.nft-management');
    Route::view('/user/collection', 'home')->name('user.collection');
    Route::view('/user/favorites', 'home')->name('user.favorites');
    Route::view('/user/history', 'home')->name('user.history');
    Route::get('/user/earnings', $earningsController)->name('user.earnings');
    Route::redirect('/user/settings', '/user/profile-settings')->name('user.settings');
    Route::redirect('/user/profile', '/user/profile-settings')->name('user.profile');
    Route::get('/user/payment', static fn () => redirect()->route('user.purchases')
        ->with('status', 'Purchases and payments are disabled during beta.'))->name('user.payment');
    Route::view('/user/pixel-coin', 'user.pixel-coin')->name('user.pixel-coin');
    Route::view('/user/resales', 'home')->name('user.resales');
    Route::post('/user/pixel-coin/top-ups', [$paymentOrderController, 'topUp'])->name('user.cash.topups');
    Route::post('/user/pixel-coin/prepaid-top-ups', [$paymentOrderController, 'prepaidTopUp'])->name('user.pixel.prepaid-topups');
    Route::post('/user/pixel-coin/withdrawals', [$paymentOrderController, 'withdraw'])->name('user.cash.withdrawals');
    Route::view('/user/terms', 'home')->name('user.terms');
    Route::get('/user/nft/{id}', static fn (string $id) => redirect()->route('week12.client.artwork', ['id' => $id]))
        ->where('id', '[A-Za-z0-9][A-Za-z0-9-]*')
        ->name('user.nft.show');
    Route::get('/user/profile-settings', [$profileController, 'edit'])->name('user.profile-settings');
    Route::post('/user/profile', [$profileController, 'update'])->name('user.profile.update');
    Route::put('/user/password', [$profileController, 'updatePassword'])->name('user.password.update');
    Route::post('/user/images', [$profileController, 'storeImage'])->name('user.images.store');
    Route::post('/user/images/{imageId}/update', [$profileController, 'updateImage'])
        ->whereNumber('imageId')
        ->name('user.images.update');
    Route::post('/user/images/{imageId}/delete', [$profileController, 'destroyImage'])
        ->whereNumber('imageId')
        ->name('user.images.destroy');
    Route::post('/user/artworks', [$artworkController, 'store'])->name('user.artworks.store');
    Route::get('/user/artworks', [$artworkController, 'index'])->name('user.artworks.index');
    Route::get('/user/artworks/{id}/edit', [$artworkController, 'edit'])
        ->where('id', '[a-z0-9-]+')
        ->name('user.artworks.edit');
    Route::post('/user/artworks/{artwork}/resale', [$resaleListingController, 'store'])
        ->where('artwork', '[a-z0-9-]+')->name('user.artworks.resale');
    Route::post('/user/purchase-orders', [$paymentOrderController, 'store'])->middleware('verified')->name('user.purchase-orders.store');
    Route::patch('/user/artworks/{id}', [$artworkController, 'update'])
        ->where('id', '[a-z0-9-]+')
        ->name('user.artworks.update');
    Route::delete('/user/artworks/{id}', [$artworkController, 'destroy'])
        ->where('id', '[a-z0-9-]+')
        ->name('user.artworks.destroy');
    Route::prefix('/api/pixel-coins')->middleware('throttle:30,1')->group(function () use ($pixelCoinController): void {
        Route::get('/', [$pixelCoinController, 'show'])->name('api.pixel-coins.show');
        Route::post('/top-ups', [$pixelCoinController, 'topUp'])->name('api.pixel-coins.top-ups');
        Route::post('/purchases', [$pixelCoinController, 'purchase'])->name('api.pixel-coins.purchases');
    });

    Route::prefix('/api')->middleware('throttle:30,1')->group(function () use ($engagementController): void {
        Route::put('/artworks/{artwork}/like', [$engagementController, 'likeArtwork'])->name('api.artworks.like');
        Route::delete('/artworks/{artwork}/like', [$engagementController, 'unlikeArtwork'])->name('api.artworks.unlike');
        Route::put('/creators/{creator}/like', [$engagementController, 'likeCreator'])->whereNumber('creator')->name('api.creators.like');
        Route::delete('/creators/{creator}/like', [$engagementController, 'unlikeCreator'])->whereNumber('creator')->name('api.creators.unlike');
        Route::put('/creators/{creator}/follow', [$engagementController, 'followCreator'])->whereNumber('creator')->name('api.creators.follow');
        Route::delete('/creators/{creator}/follow', [$engagementController, 'unfollowCreator'])->whereNumber('creator')->name('api.creators.unfollow');
    });
});

Route::middleware(['auth', 'can:manage-admin'])->group(function () use ($week12ArtworkStore, $sitePageContentController, $paymentOrderController, $resaleListingController, $adminController): void {
    Route::redirect('/admin/content', '/admin/content/about')->name('admin.content');
    Route::get('/admin/content/{slug}', [$sitePageContentController, 'edit'])
        ->whereIn('slug', array_keys(SitePageContent::definitions()))
        ->name('admin.content.edit');
    Route::post('/admin/content/{slug}', [$sitePageContentController, 'update'])
        ->whereIn('slug', array_keys(SitePageContent::definitions()))
        ->name('admin.content.update');

    Route::redirect('/admin', '/week12/admin')->name('admin');
    Route::get('/admin/payment-orders', function () {
        return view('admin.payment-orders', [
            'orders' => \App\Models\PaymentOrder::query()->with(['buyer', 'seller', 'artwork'])->latest()->limit(100)->get(),
            'resales' => DB::table('resale_listings')->join('artworks', 'artworks.id', '=', 'resale_listings.artwork_id')
                ->join('users', 'users.id', '=', 'resale_listings.seller_id')
                ->select('resale_listings.*', 'artworks.title', 'users.name as seller_name')->latest('resale_listings.created_at')->limit(100)->get(),
        ]);
    })->name('admin.payment-orders');
    Route::get('/admin/payment-orders/{order}/proof', function (\App\Models\PaymentOrder $order) {
        abort_unless($order->proof_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($order->proof_path), 404);
        return \Illuminate\Support\Facades\Storage::disk('local')->response($order->proof_path);
    })->name('admin.payment-orders.proof');
    Route::get('/admin/resales/{listing}/evidence', function (int $listing) {
        $path = DB::table('resale_listings')->where('id', $listing)->value('evidence_path');
        abort_unless(is_string($path) && \Illuminate\Support\Facades\Storage::disk('local')->exists($path), 404);
        return \Illuminate\Support\Facades\Storage::disk('local')->response($path);
    })->name('admin.resales.evidence');
    Route::post('/admin/payment-orders/{order}/review', [$paymentOrderController, 'review'])->name('admin.payment-orders.review');
    Route::post('/admin/resales/{listing}/review', [$resaleListingController, 'review'])->whereNumber('listing')->name('admin.resales.review');

    Route::get('/admin/users', [$adminController, 'users'])->name('admin.users');
    Route::post('/admin/users/{user}/role', [$adminController, 'updateRole'])->whereNumber('user')->name('admin.users.role');
    Route::post('/admin/artworks/{id}/price', [$adminController, 'updateArtworkPrice'])
        ->where('id', '[a-z0-9-]+')->name('admin.artworks.price');

    Route::get('/week12/admin', function () use ($week12ArtworkStore) {
        return view('week12.admin.dashboard', [
            'counts' => $week12ArtworkStore()->counts(),
            'artworks' => $week12ArtworkStore()->recent(),
            'userCount' => \App\Models\User::query()->count(),
            'unverifiedUserCount' => \App\Models\User::query()->whereNull('email_verified_at')->count(),
            'paymentCounts' => \App\Models\PaymentOrder::query()
                ->selectRaw('status, COUNT(*) AS total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'pendingResaleCount' => DB::table('resale_listings')->where('status', 'pending_review')->count(),
        ]);
    })->name('week12.admin');

    Route::get('/week12/admin/artworks', function () use ($week12ArtworkStore) {
        return view('week12.admin.artworks', [
            'artworks' => $week12ArtworkStore()->all(),
        ]);
    })->name('week12.admin.artworks');

    Route::post('/week12/admin/artworks/{id}/status', function (string $id) use ($week12ArtworkStore) {
        try {
            $week12ArtworkStore()->updateStatus($id, (string) request()->input('status'));
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['listing' => $exception->getMessage()]);
        } catch (OutOfBoundsException) {
            abort(404);
        }

        return redirect()->route('week12.admin.artworks');
    })->where('id', '[a-z0-9-]+')->name('week12.admin.artworks.status');

    Route::post('/week12/admin/artworks/{id}/delete', function (string $id) use ($week12ArtworkStore) {
        try {
            $week12ArtworkStore()->delete($id);
        } catch (OutOfBoundsException) {
            abort(404);
        }

        return redirect()->route('week12.admin.artworks');
    })->where('id', '[a-z0-9-]+')->name('week12.admin.artworks.delete');
});

Route::get('/week12/artworks/{id}', function (string $id) use ($week12ArtworkStore, $engagementController) {
    $artwork = $week12ArtworkStore()->find($id);
    abort_if($artwork === null, 404);

    $model = Artwork::query()->findOrFail($id);
    if ($artwork['status'] === 'Listed') {
        app($engagementController)->viewArtwork(request(), $model);
    }
    $artwork['views_count'] = DB::table('artwork_views')->where('artwork_id', $id)->count();
    $artwork['likes_count'] = DB::table('artwork_likes')->where('artwork_id', $id)->count();
    $artwork['liked'] = auth()->check() && DB::table('artwork_likes')
        ->where('user_id', auth()->id())
        ->where('artwork_id', $id)
        ->exists();

    return view('week12.client.show', ['artwork' => $artwork]);
})->where('id', '[a-z0-9-]+')->name('week12.client.artwork');
