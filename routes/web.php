<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Support\SitePageContent;

require_once base_path('app/Week12Helpers.php');
require_once base_path('app/ArtworkRepository.php');

$authController = \App\Http\Controllers\AuthController::class;
$artworkController = \App\Http\Controllers\ArtworkController::class;
$profileController = \App\Http\Controllers\UserProfileController::class;
$pixelCoinController = \App\Http\Controllers\PixelCoinController::class;
$sitePageContentController = \App\Http\Controllers\SitePageContentController::class;
$week12ArtworkStore = static fn (): ArtworkRepository => new ArtworkRepository(DB::connection()->getPdo());

Route::view('/', 'home')->name('home');
Route::view('/about', 'about')->name('about');
Route::view('/nft/{id}', 'home')->where('id', '[A-Za-z0-9][A-Za-z0-9-]*')->name('nft.guest');
Route::view('/login', 'login')->name('login');
Route::post('/login', [$authController, 'login'])->name('login.store');
Route::get('/register', [$authController, 'create'])->name('register');
Route::post('/register', [$authController, 'store'])->name('register.store');
Route::get('/create-account', [$authController, 'create'])->name('create-account');
Route::post('/logout', [$authController, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () use ($profileController, $artworkController, $pixelCoinController): void {
    Route::view('/user', 'home')->name('user');
    Route::view('/user/dashboard', 'home')->name('user.dashboard');
    Route::view('/user/create', 'home')->name('user.create');
    Route::view('/user/notifications', 'home')->name('user.notifications');
    Route::view('/user/about', 'home')->name('user.about');
    Route::view('/user/purchases', 'home')->name('user.purchases');
    Route::view('/user/nft-management', 'home')->name('user.nft-management');
    Route::view('/user/collection', 'home')->name('user.collection');
    Route::view('/user/favorites', 'home')->name('user.favorites');
    Route::view('/user/history', 'home')->name('user.history');
    Route::redirect('/user/settings', '/user/profile-settings')->name('user.settings');
    Route::redirect('/user/profile', '/user/profile-settings')->name('user.profile');
    Route::view('/user/payment', 'home')->name('user.payment');
    Route::view('/user/pixel-coin', 'home')->name('user.pixel-coin');
    Route::view('/user/terms', 'home')->name('user.terms');
    Route::view('/user/nft/{id}', 'home')->where('id', '[A-Za-z0-9][A-Za-z0-9-]*')->name('user.nft.show');
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
});

Route::middleware(['auth', 'can:manage-admin'])->group(function () use ($week12ArtworkStore, $sitePageContentController, $artworkController): void {
    Route::redirect('/admin/content', '/admin/content/about')->name('admin.content');
    Route::get('/admin/content/{slug}', [$sitePageContentController, 'edit'])
        ->whereIn('slug', array_keys(SitePageContent::definitions()))
        ->name('admin.content.edit');
    Route::post('/admin/content/{slug}', [$sitePageContentController, 'update'])
        ->whereIn('slug', array_keys(SitePageContent::definitions()))
        ->name('admin.content.update');

    Route::redirect('/admin', '/week12/admin')->name('admin');

    Route::get('/week12/admin', function () use ($week12ArtworkStore) {
        return view('week12.admin.dashboard', [
            'counts' => $week12ArtworkStore()->counts(),
            'artworks' => $week12ArtworkStore()->recent(),
        ]);
    })->name('week12.admin');

    Route::get('/week12/admin/artworks', function () use ($week12ArtworkStore) {
        return view('week12.admin.artworks', [
            'artworks' => $week12ArtworkStore()->all(),
            'creators' => DB::table('users')->orderBy('name')->get(['id', 'name']),
        ]);
    })->name('week12.admin.artworks');

    Route::post('/week12/admin/artworks', function () use ($week12ArtworkStore, $artworkController) {
        if (request()->hasFile('image_upload')) {
            return app($artworkController)->storeAdmin(request());
        }

        try {
            $id = $week12ArtworkStore()->create(request()->all());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['listing' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('week12.client.artwork', ['id' => $id]);
    })->name('week12.admin.artworks.store');

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

Route::get('/week12', function () use ($week12ArtworkStore) {
    return view('week12.client.index', [
        'artworks' => $week12ArtworkStore()->listed(),
    ]);
})->name('week12.client');

Route::get('/week12/artworks/{id}', function (string $id) use ($week12ArtworkStore) {
    $artwork = $week12ArtworkStore()->find($id);
    abort_if($artwork === null, 404);

    return view('week12.client.show', ['artwork' => $artwork]);
})->where('id', '[a-z0-9-]+')->name('week12.client.artwork');
