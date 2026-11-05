<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $creator->name }} — Pixmoree creators</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
</head>
<body class="creator-page">
<header class="profile-settings-topbar">
    <div class="profile-settings-topbar-inner">
        <a class="profile-settings-brand" href="/" aria-label="Pixmoree home">
            <img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree">
        </a>
        <nav class="profile-settings-nav" aria-label="Creator navigation">
            <a href="/week12"><i class="bi bi-compass" aria-hidden="true"></i> Explore</a>
            <a class="profile-settings-back-link" href="/user"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to account</a>
            @auth
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="profile-settings-logout" type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Log out</button>
                </form>
            @else
                <a class="profile-settings-back-link" href="{{ route('login') }}">Log in</a>
            @endauth
        </nav>
    </div>
</header>

<main class="creator-main">
    <section class="creator-profile-hero" aria-labelledby="creator-name" data-engagement-metrics="creator" data-entity-id="{{ $creator->id }}" data-views="{{ $engagement['views'] }}" data-likes="{{ $engagement['likes'] }}" data-followers="{{ $engagement['followers'] }}">
        @if ($creator->banner_path)
            <img class="creator-profile-banner" src="{{ asset($creator->banner_path) }}" alt="">
        @endif
        <div class="creator-profile-identity">
            <span class="creator-profile-avatar">
                @if ($creator->avatar_path)
                    <img src="{{ asset($creator->avatar_path) }}" alt="">
                @else
                    {{ strtoupper(substr($creator->name, 0, 1)) }}
                @endif
            </span>
            <div class="creator-profile-name">
                <p class="profile-settings-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> PIXMOREE CREATOR</p>
                <h1 id="creator-name">{{ $creator->name }}</h1>
                <p>{{ '@'.strtolower(str_replace(' ', '-', $creator->name)) }}</p>
            </div>
        </div>
        @if ($creator->bio)
            <p class="creator-profile-bio">{{ $creator->bio }}</p>
        @endif
        <div class="creator-profile-actions">
            @auth
                @if (auth()->id() !== $creator->id)
                    <button class="creator-action-button" type="button" data-engagement-like="creator" data-entity-id="{{ $creator->id }}" data-liked="{{ $engagement['liked'] ? 'true' : 'false' }}" aria-pressed="{{ $engagement['liked'] ? 'true' : 'false' }}">
                        <i class="bi {{ $engagement['liked'] ? 'bi-heart-fill' : 'bi-heart' }}" aria-hidden="true"></i><span>Like</span>
                    </button>
                    <button class="creator-action-button creator-follow-button" type="button" data-engagement-follow="creator" data-entity-id="{{ $creator->id }}" data-following="{{ $engagement['following'] ? 'true' : 'false' }}" aria-pressed="{{ $engagement['following'] ? 'true' : 'false' }}">
                        <i class="bi {{ $engagement['following'] ? 'bi-person-check' : 'bi-person-plus' }}" aria-hidden="true"></i><span>{{ $engagement['following'] ? 'Following' : 'Follow' }}</span>
                    </button>
                @endif
            @else
                <a class="creator-action-button" href="{{ route('login') }}"><i class="bi bi-heart" aria-hidden="true"></i> Log in to like or follow</a>
            @endauth
            <p class="engagement-feedback" data-engagement-feedback role="status" aria-live="polite" hidden></p>
        </div>
        <div class="creator-profile-metrics">
            <div><small>Profile views</small><strong data-engagement-views>{{ number_format($engagement['views']) }}</strong></div>
            <div><small>Likes</small><strong data-engagement-likes>{{ number_format($engagement['likes']) }}</strong></div>
            <div><small>Followers</small><strong data-engagement-followers>{{ number_format($engagement['followers']) }}</strong></div>
            <div><small>Listed artwork</small><strong>{{ number_format($artworks->count()) }}</strong></div>
        </div>
    </section>

    <section class="creator-artworks-section" aria-labelledby="creator-artworks-heading">
        <div class="creator-artworks-heading">
            <div><p class="profile-settings-eyebrow">COLLECTION</p><h2 id="creator-artworks-heading">{{ $creator->name }}’s artwork</h2></div>
            <span>{{ number_format($artworks->count()) }} {{ \Illuminate\Support\Str::plural('piece', $artworks->count()) }}</span>
        </div>
        <div class="creator-artwork-grid">
            @forelse ($artworks as $artwork)
                @php($artworkLiked = in_array($artwork->id, $likedArtworkIds, true))
                <article class="creator-artwork-card" data-engagement-metrics="artwork" data-entity-id="{{ $artwork->id }}" data-views="{{ $artwork->views_count }}" data-likes="{{ $artwork->likes_count }}">
                    <a class="creator-artwork-image" href="{{ route('week12.client.artwork', ['id' => $artwork->id]) }}">
                        <img src="{{ asset(str_starts_with($artwork->image, 'storage/') ? $artwork->image : 'assets/' . $artwork->image) }}" alt="{{ $artwork->title }}">
                    </a>
                    <div class="creator-artwork-details">
                        <div><h3>{{ $artwork->title }}</h3><span>{{ number_format((float) $artwork->price, 2) }} ETH</span></div>
                        <p>{{ $artwork->category }}</p>
                        <div class="creator-artwork-metrics">
                            <span><i class="bi bi-eye" aria-label="Views"></i> <span data-engagement-views>{{ number_format($artwork->views_count) }}</span></span>
                            @auth
                                <button type="button" data-engagement-like="artwork" data-entity-id="{{ $artwork->id }}" data-liked="{{ $artworkLiked ? 'true' : 'false' }}" aria-pressed="{{ $artworkLiked ? 'true' : 'false' }}"><i class="bi {{ $artworkLiked ? 'bi-heart-fill' : 'bi-heart' }}" aria-hidden="true"></i> <span data-engagement-likes>{{ number_format($artwork->likes_count) }}</span></button>
                            @else
                                <span><i class="bi bi-heart" aria-label="Likes"></i> <span data-engagement-likes>{{ number_format($artwork->likes_count) }}</span></span>
                            @endauth
                        </div>
                    </div>
                </article>
            @empty
                <p class="creator-empty-state">This creator has no listed artwork yet.</p>
            @endforelse
        </div>
    </section>
</main>
<script src="{{ asset('js/engagement.js') }}?v=20261104-engagement"></script>
</body>
</html>