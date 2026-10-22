<?php

use Illuminate\Support\Facades\Route;

$week12Artworks = [
    [
        'id' => 'neon-wanderer',
        'title' => 'Neon Wanderer',
        'creator' => 'pixel.dreams',
        'category' => 'Space',
        'price' => '0.86',
        'image' => 'assets/anime-style-character-space.jpg',
        'description' => 'A lone traveler drifts through a violet galaxy in this fictional digital-art collectible.',
        'status' => 'Listed',
    ],
    [
        'id' => 'moonlit-guardian',
        'title' => 'Moonlit Guardian',
        'creator' => 'neko.studio',
        'category' => 'Characters',
        'price' => '1.24',
        'image' => 'assets/cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png',
        'description' => 'A fantasy guardian watches over a quiet moonlit world. All listing details are sample data.',
        'status' => 'Listed',
    ],
    [
        'id' => 'starlight-drift',
        'title' => 'Starlight Drift',
        'creator' => 'cosmic.art',
        'category' => 'Space',
        'price' => '1.52',
        'image' => 'assets/anime-style-character-space (2).jpg',
        'description' => 'An imagined journey across a glowing nebula, presented as a non-functional demo listing.',
        'status' => 'Review',
    ],
];

Route::view('/', 'home')->name('home');
Route::get('/guest-copy', function () {
    return response()->file(base_path('guest-copy/index.html'));
})->name('guest-copy');
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

Route::get('/week12', function () use ($week12Artworks) {
    return view('week12.client.index', ['artworks' => $week12Artworks]);
})->name('week12.client');

Route::get('/week12/artworks/{id}', function (string $id) use ($week12Artworks) {
    $artwork = collect($week12Artworks)->firstWhere('id', $id);
    abort_if($artwork === null, 404);

    return view('week12.client.show', ['artwork' => $artwork]);
})->where('id', '[a-z0-9-]+')->name('week12.client.artwork');

Route::view('/week12/admin', 'week12.admin.dashboard')
    ->name('week12.admin');

Route::get('/week12/admin/artworks', function () use ($week12Artworks) {
    return view('week12.admin.artworks', ['artworks' => $week12Artworks]);
})->name('week12.admin.artworks');
