<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NFT Store — Pixmoree</title>
    <meta name="description" content="Browse all listed digital artworks on Pixmoree.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}?v=20261111-store" rel="stylesheet">
    <link href="{{ asset('css/typography.css') }}?v=20261111" rel="stylesheet">
</head>
<body class="store-page">
<header class="store-header">
    <a class="store-brand" href="{{ route('user') }}">
        <img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree">
    </a>
    <nav aria-label="Main navigation">
        <a href="{{ route('user') }}">Home</a>
        <a class="active" href="{{ route('store') }}" aria-current="page">Store</a>
        <a href="{{ route('user.profile-settings') }}">Profile</a>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Log out</button>
        </form>
    </nav>
</header>
<main class="store-main">
    <header class="store-heading">
        <p class="store-eyebrow"><i class="bi bi-shop-window" aria-hidden="true"></i> PIXMOREE MARKETPLACE</p>
        <h1>NFT Store</h1>
        <p>Explore every currently listed artwork from our creators.</p>
    </header>
    <section class="store-grid" aria-label="Listed NFTs">
        @forelse ($artworks as $artwork)
            <article class="store-card">
                <a class="store-card-image" href="{{ route('week12.client.artwork', ['id' => $artwork->id]) }}">
                    <img src="{{ asset(str_starts_with($artwork->image, 'storage/') ? $artwork->image : 'assets/' . $artwork->image) }}" alt="{{ $artwork->title }} digital artwork" loading="lazy">
                </a>
                <div class="store-card-content">
                    <p class="store-card-category">{{ $artwork->category }}</p>
                    <h2><a href="{{ route('week12.client.artwork', ['id' => $artwork->id]) }}">{{ $artwork->title }}</a></h2>
                    <p class="store-card-creator">By {{ $artwork->user?->name ?? $artwork->creator }}</p>
                    <div class="store-card-details">
                        <span><small>Price</small><strong>{{ number_format((float) $artwork->price, 2) }} ETH</strong></span>
                        <span><small>Edition</small><strong>{{ number_format((int) $artwork->item_count) }}</strong></span>
                    </div>
                    @if ((int) $artwork->user_id === (int) auth()->id())
                        <span class="store-owned-note">Your artwork</span>
                    @elseif (config('payments.enabled'))
                        <a class="store-buy-button" href="{{ route('user.checkout', ['artworkId' => $artwork->id]) }}">Buy NFT</a>
                    @else
                        <button class="store-buy-button" type="button" disabled aria-describedby="store-buy-beta-{{ $loop->index }}">Buy NFT</button>
                        <small class="store-buy-note" id="store-buy-beta-{{ $loop->index }}">Checkout is currently unavailable.</small>
                    @endif
                </div>
            </article>
        @empty
            <p class="store-empty">No NFTs are listed yet. Please check back soon.</p>
        @endforelse
    </section>
    <section class="store-resale-section" aria-labelledby="store-resale-heading">
        <header class="store-heading">
            <p class="store-eyebrow"><i class="bi bi-arrow-left-right" aria-hidden="true"></i> CURRENT OWNER LISTINGS</p>
            <h2 id="store-resale-heading">Resale NFTs</h2>
            <p>Previously purchased artworks available from their verified current owners.</p>
        </header>
        @include('partials.resale-listings', ['resaleListings' => $resaleListings])
    </section>
</main>
</body>
</html>
