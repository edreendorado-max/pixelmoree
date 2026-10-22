<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

require_once base_path('app/Week12Helpers.php');
require_once base_path('app/ArtworkRepository.php');

$week12ArtworkStore = static fn (): ArtworkRepository => new ArtworkRepository(DB::connection()->getPdo());

Route::view('/', 'home')->name('home');
Route::view('/about', 'about')->name('about');
Route::view('/admin', 'admin')->name('admin');
Route::view('/user', 'home')->name('user');
Route::view('/user/dashboard', 'home')->name('user.dashboard');
Route::view('/user/create', 'home')->name('user.create');
Route::view('/user/settings', 'home')->name('user.settings');
Route::view('/user/profile-settings', 'home')->name('user.profile-settings');
Route::view('/user/notifications', 'home')->name('user.notifications');
Route::view('/user/about', 'home')->name('user.about');
Route::view('/user/purchases', 'home')->name('user.purchases');
Route::view('/user/nft-management', 'home')->name('user.nft-management');
Route::view('/user/collection', 'home')->name('user.collection');
Route::view('/user/favorites', 'home')->name('user.favorites');
Route::view('/user/history', 'home')->name('user.history');
Route::view('/user/profile', 'home')->name('user.profile');
Route::view('/user/payment', 'home')->name('user.payment');
Route::view('/user/terms', 'home')->name('user.terms');
Route::view('/user/nft/{id}', 'home')->where('id', '[A-Za-z0-9][A-Za-z0-9-]*')->name('user.nft.show');
Route::view('/nft/{id}', 'home')->where('id', '[A-Za-z0-9][A-Za-z0-9-]*')->name('nft.guest');
Route::view('/login', 'login')->name('login');
Route::view('/register', 'register')->name('register');
Route::view('/create-account', 'register')->name('create-account');

Route::get('/week12', function () use ($week12ArtworkStore) {
    return view('week12.client.index', [
        'artworks' => $week12ArtworkStore()->all(),
    ]);
})->name('week12.client');

Route::get('/week12/artworks/{id}', function (string $id) use ($week12ArtworkStore) {
    $artwork = $week12ArtworkStore()->find($id);
    abort_if($artwork === null, 404);

    return view('week12.client.show', ['artwork' => $artwork]);
})->where('id', '[a-z0-9-]+')->name('week12.client.artwork');

Route::get('/week12/admin', function () use ($week12ArtworkStore) {
    return view('week12.admin.dashboard', [
        'counts' => $week12ArtworkStore()->counts(),
        'artworks' => $week12ArtworkStore()->recent(),
    ]);
})->name('week12.admin');

Route::get('/week12/admin/artworks', function () use ($week12ArtworkStore) {
    return view('week12.admin.artworks', [
        'artworks' => $week12ArtworkStore()->all(),
    ]);
})->name('week12.admin.artworks');

Route::post('/week12/admin/artworks', function () use ($week12ArtworkStore) {
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
