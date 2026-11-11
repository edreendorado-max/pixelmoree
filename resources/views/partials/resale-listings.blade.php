<div class="store-grid resale-grid">
    @forelse ($resaleListings as $listing)
        <article class="store-card resale-card">
            <a class="store-card-image" href="{{ route('week12.client.artwork', ['id' => $listing->artwork->id]) }}">
                <img src="{{ asset(str_starts_with($listing->artwork->image, 'storage/') ? $listing->artwork->image : 'assets/' . $listing->artwork->image) }}" alt="{{ $listing->artwork->title }} digital artwork" loading="lazy">
            </a>
            <div class="store-card-content">
                <p class="store-card-category">Resale · {{ $listing->artwork->category }}</p>
                <h2><a href="{{ route('week12.client.artwork', ['id' => $listing->artwork->id]) }}">{{ $listing->artwork->title }}</a></h2>
                <p class="store-card-creator">Listed by {{ $listing->seller?->name ?? 'Current owner' }}</p>
                <div class="store-card-details">
                    <span><small>Resale price</small><strong>PHP {{ number_format($listing->price_minor / 100, 2) }}</strong></span>
                    <span><small>Edition</small><strong>1</strong></span>
                </div>
                @if ((int) $listing->seller_id === (int) auth()->id())
                    <span class="store-owned-note">Your resale listing</span>
                @elseif (config('payments.enabled') && auth()->check())
                    <a class="store-buy-button" href="{{ route('user.checkout', ['artworkId' => $listing->artwork->id]) }}">Buy resale</a>
                @elseif (config('payments.enabled'))
                    <a class="store-buy-button" href="{{ route('login') }}">Log in to buy</a>
                @else
                    <button class="store-buy-button" type="button" disabled>Resale checkout unavailable</button>
                @endif
            </div>
        </article>
    @empty
        <p class="store-empty">No approved resale listings are available right now.</p>
    @endforelse
</div>
