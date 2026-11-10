@php($account = auth()->user())
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $homeContent['meta_title'] }}</title>
    <meta name="description" content="{{ $homeContent['meta_description'] }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}?v=20261105-for-you-dashboard" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark site-nav guest-nav">
  <div class="container-fluid px-3 px-lg-5">
    <a class="navbar-brand" href="{{ $account ? route('user') : '#home' }}" aria-label="Pixmoree home"><img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree" class="brand-logo"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="mainNav">
      <div class="guest-links mx-lg-auto my-3 my-lg-0">
        <a class="member-create-link member-only-nav-link" href="/user/create" data-member-nav="create" hidden><i class="bi bi-plus-lg"></i><span>Create</span></a>
        <a class="active" href="#home" data-member-nav="home"><i class="bi bi-house-door"></i><span>Home</span></a>
        <a href="#explore" data-member-nav="collection"><i class="bi bi-images"></i><span>Gallery</span></a>
        <a class="guest-only-nav-link" href="#trending" data-member-nav="trending"><i class="bi bi-graph-up-arrow"></i><span>Trending</span></a>
        <a class="guest-only-nav-link" href="/about"><i class="bi bi-info-circle"></i><span>About</span></a>
        <a href="/user/purchases" class="member-only-nav-link" data-member-nav="history" hidden><i class="bi bi-receipt"></i><span>Purchases</span></a>
        <a href="/user/earnings" class="member-only-nav-link" data-member-nav="earnings" hidden><i class="bi bi-graph-up-arrow"></i><span>Earnings</span></a>
        <a href="/user/resales" class="member-only-nav-link" data-member-nav="resales" hidden><i class="bi bi-arrow-left-right"></i><span>Resale Artworks</span></a>
        <a href="/user/terms" class="member-only-nav-link" data-member-nav="terms" hidden><i class="bi bi-shield-check"></i><span>Terms &amp; Originality</span></a>
        <a href="/user/profile-settings" class="member-only-nav-link" data-member-nav="profile-settings" hidden><i class="bi bi-person-gear"></i><span>Profile Settings</span></a>
        <a href="/user/about" class="member-only-nav-link" data-member-nav="about" hidden><i class="bi bi-info-circle"></i><span>About</span></a>
      </div>
      <div class="nav-actions">
        <a class="login-link" href="/login">Log in</a>
        <div class="guest-search">
          <button class="search-icon" type="button" aria-label="Open artwork search" aria-expanded="false" aria-controls="gallerySearch"><i class="bi bi-search"></i></button>
          <input id="gallerySearch" class="search-input" type="search" placeholder="Search artwork..." aria-label="Search artwork" tabindex="-1">
        </div>
        <a href="/user/pixel-coin" class="member-pixel-coin-link member-only-nav-link" data-member-nav="pixel-coin" aria-label="Pixel Coin wallet" @if (!$account) hidden @endif><i class="bi bi-coin" aria-hidden="true"></i><span data-pixelcoin-balance>{{ number_format((int) ($account?->pixel_coin_balance ?? 0)) }}</span><small>PIXEL</small><span class="member-pixel-coin-status" id="memberPixelCoinNavStatus" role="status" aria-label="Wallet unavailable" hidden>!</span></a>
        <div class="member-profile" id="memberProfile" hidden>
          <button class="member-profile-toggle" id="memberProfileToggle" type="button" aria-expanded="false" aria-controls="memberProfileMenu"><span class="member-avatar" id="memberNavAvatar"><span class="member-avatar-initial">{{ strtoupper(substr($account?->name ?? 'A', 0, 1)) }}</span></span><span id="memberNavName">{{ $account?->name ?? 'Account' }}</span><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
          <div class="member-profile-menu" id="memberProfileMenu" hidden>
            <span class="member-menu-heading">SIGNED IN AS {{ strtoupper($account?->name ?? 'ACCOUNT') }}</span>
            <a href="/user" data-member-action="dashboard"><i class="bi bi-house"></i> Home</a>
            <a href="/user/me" data-member-action="me"><i class="bi bi-person-circle"></i> Me</a>
            <a href="/user/create" data-member-action="create"><i class="bi bi-plus-circle"></i> Create</a>
            <a href="/user/purchases" data-member-action="history"><i class="bi bi-receipt"></i> Purchases</a>
            <a href="/user/earnings" data-member-action="earnings"><i class="bi bi-graph-up-arrow"></i> Earnings balance</a>
            <a href="/user/pixel-coin" data-member-action="pixel-coin"><i class="bi bi-coin"></i> Pixel Coin wallet</a>
            <a href="/user/terms" data-member-action="terms"><i class="bi bi-shield-check"></i> Terms &amp; Originality</a>
            <a href="/user/profile-settings" data-member-action="profile-settings"><i class="bi bi-person-gear"></i> Profile Settings</a>
            <a href="/user/about" data-member-action="about"><i class="bi bi-info-circle"></i> About Pixmoree</a>
            @if ($account?->isAdmin())
              <a href="/admin" data-member-action="admin"><i class="bi bi-shield-lock"></i> Admin</a>
              <a href="/admin/content/about" data-member-action="admin"><i class="bi bi-pencil-square"></i> Website content</a>
            @endif
            <a href="#explore" class="member-menu-explore"><i class="bi bi-shop"></i> Explore NFTs</a>
            <form method="post" action="{{ route('logout') }}">
              @csrf
              <button class="member-menu-exit" type="submit" data-member-action="logout"><i class="bi bi-box-arrow-right"></i> Log out</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>
<section id="home" class="guest-hero">
  <div class="hero-stars hero-stars-one" aria-hidden="true">✦</div><div class="hero-stars hero-stars-two" aria-hidden="true">✦</div>
  <div class="hero-content">
    <h1>{{ $homeContent['hero_heading'] }}</h1><p class="hero-tagline">{{ $homeContent['hero_tagline'] }}</p>
    <ul class="hero-benefits">
      <li><i class="bi bi-check-circle"></i><span>{{ $homeContent['hero_benefits'][0] }}</span></li>
      <li><i class="bi bi-check-circle"></i><span>{{ $homeContent['hero_benefits'][1] }}</span></li>
    </ul>
    <a class="btn hero-explore" href="#explore">{{ $homeContent['hero_cta'] }}</a>
  </div>
  <img class="hero-background-art" src="{{ asset($homeContent['hero_background']) }}" alt="" aria-hidden="true">
</section>
<main class="container-fluid px-3 px-lg-5 py-4">
    <section class="member-dashboard" id="memberWelcome" hidden aria-label="User account pages">
      <header class="member-dashboard-heading">
        <div><span class="member-welcome-kicker"><i class="bi bi-stars"></i> YOUR PIXMOREE ACCOUNT</span><h2>Welcome back, <span id="memberWelcomeName">{{ $account?->name ?? 'friend' }}</span>!</h2><p>Discover art, keep track of your collection, and manage your account.</p></div>
        <a class="member-explore-link" href="#explore"><i class="bi bi-compass"></i> Explore NFTs</a>
      </header>
      <div class="member-dashboard-layout">
        <div class="member-dashboard-content">
          <section class="member-dashboard-panel" data-member-panel-content="home">
            <section class="member-panel-card member-for-you-panel">
              <div class="member-panel-heading"><div><h3>For You</h3><p>Recommendations based on your likes, purchases, and artwork views. Updated with your latest activity.</p></div><a href="/user#explore">Explore <i class="bi bi-arrow-up-right"></i></a></div>
              <div class="member-recent-grid member-for-you-grid" id="memberForYouItems">
                @forelse ($forYouArtworks as $artwork)
                  <a class="member-art-tile member-for-you-tile" href="{{ route('week12.client.artwork', ['id' => $artwork->id]) }}">
                    <span class="member-art-tile-image"><img src="{{ asset(str_starts_with($artwork->image, 'storage/') ? $artwork->image : 'assets/' . $artwork->image) }}" alt="{{ $artwork->title }}" loading="lazy"></span>
                    <span class="member-art-tile-details"><strong>{{ $artwork->title }}</strong><small>By {{ $artwork->creator }}</small><span>{{ number_format((float) $artwork->price, 2) }} ETH</span><small class="member-for-you-engagement"><i class="bi bi-eye" aria-label="Views"></i> {{ number_format($artwork->views_count) }} <i class="bi bi-heart" aria-label="Likes"></i> {{ number_format($artwork->likes_count) }}</small></span>
                  </a>
                @empty
                  <p class="member-empty-state">New artwork will appear here as creators publish listings.</p>
                @endforelse
              </div>
            </section>
            <section class="member-panel-card member-price-dashboard">
              <div class="member-panel-heading"><div><h3>Price analytics</h3><p>Only verified marketplace transactions can support a market estimate.</p></div><span class="member-demo-label">UNAVAILABLE</span></div>
              <p class="member-empty-state">Price analytics are hidden during beta because payment and sales processing are disabled. Listed prices are set by creators.</p>
            </section>
          </section>
          <section class="member-dashboard-panel active" data-member-panel-content="me">
            <div class="member-panel-heading"><div><h3>Me</h3><p>Your profile, uploads, and collected artwork.</p></div><span class="member-demo-label">YOUR ACCOUNT</span></div>
            <article class="member-dashboard-profile-card">
              @if ($account?->banner_path)
                <img class="member-dashboard-banner" id="memberDashboardBanner" src="{{ asset($account->banner_path) }}" alt="">
              @else
                <img class="member-dashboard-banner" id="memberDashboardBanner" alt="" hidden>
              @endif
              <span class="member-dashboard-avatar" id="memberDashboardAvatar"><span class="member-avatar-initial">{{ strtoupper(substr($account?->name ?? 'A', 0, 1)) }}</span><img id="memberDashboardAvatarImage" alt="" hidden></span>
              <div class="member-dashboard-identity"><small>YOUR PROFILE</small><strong id="memberDashboardProfileName">{{ $account?->name ?? 'Account' }}</strong><span id="memberDashboardUsername">{{ '@'.($account?->username ?? strtolower(str_replace(' ', '-', $account?->name ?? 'account'))) }}</span></div>
              <div class="member-dashboard-profile-stats">
                <div><small>Profile views</small><strong>{{ number_format($account?->profile_views_count ?? 0) }}</strong></div>
                <div><small>Profile likes</small><strong>{{ number_format($account?->profile_likes_count ?? 0) }}</strong></div>
                <div><small>Followers</small><strong>{{ number_format($account?->followers_count ?? 0) }}</strong></div>
              </div>
            </article>
            <div class="member-stat-grid">
              <article class="member-stat-card"><span class="member-stat-icon"><i class="bi bi-heart-fill"></i></span><div><small>Favorite artwork</small><strong id="memberWelcomeFavorites">{{ $likedArtworks->count() }}</strong></div><button type="button" data-member-panel-link="favorites" aria-label="Open favorites"><i class="bi bi-arrow-up-right"></i></button></article>
              <article class="member-stat-card"><span class="member-stat-icon"><i class="bi bi-images"></i></span><div><small>Owned artwork</small><strong id="memberOwnedCount">{{ $ownedArtworks->count() }}</strong></div><button type="button" data-member-panel-link="collection" aria-label="Open collection"><i class="bi bi-arrow-up-right"></i></button></article>
              <article class="member-stat-card"><span class="member-stat-icon"><i class="bi bi-receipt"></i></span><div><small>Purchase orders</small><strong id="memberPurchaseCount">{{ $purchaseOrders->count() }}</strong></div><button type="button" data-member-panel-link="history" aria-label="Open purchase history"><i class="bi bi-arrow-up-right"></i></button></article>
            </div>
            <section class="member-panel-card member-collection-card">
              <div class="member-panel-heading"><div><h3>My work &amp; collection</h3><p>Manage your uploads, owned artwork, and favorites.</p></div><a href="/user/create" class="member-dashboard-create"><i class="bi bi-plus-lg"></i> Create</a></div>
              <div class="member-collection-tabs" role="tablist" aria-label="Me sections">
                <button type="button" role="tab" id="memberCollectionTabUploads" data-collection-tab="uploads" aria-controls="memberCollectionUploadsPanel">My Uploads</button>
                <button type="button" role="tab" id="memberCollectionTabOwned" data-collection-tab="owned" aria-controls="memberCollectionOwnedPanel">Owned <span id="memberCollectionCount">{{ $ownedArtworks->count() }}</span></button>
                <button type="button" role="tab" id="memberCollectionTabFavorites" data-collection-tab="favorites" aria-controls="memberCollectionFavoritesPanel">My Favorites <span id="memberFavoritePanelCount">{{ $likedArtworks->count() }}</span></button>
              </div>
              <div class="member-collection-tab-panel" id="memberCollectionUploadsPanel" role="tabpanel" aria-labelledby="memberCollectionTabUploads" hidden>
                <div class="member-owned-grid" id="memberCollectionUploads">
                  @forelse ($userArtworks as $artwork)
                    <a class="member-art-tile" href="{{ route('user.artworks.edit', ['id' => $artwork->id]) }}">
                      <span class="member-art-tile-image"><img src="{{ asset($artwork->image) }}" alt="{{ $artwork->title }}" loading="lazy"></span>
                      <span class="member-art-tile-details"><strong>{{ $artwork->title }}</strong><small>{{ $artwork->status }}</small><span>{{ number_format((float) $artwork->price, 2) }} ETH</span></span>
                    </a>
                  @empty
                    <p class="member-empty-state">You have not uploaded any artwork yet. Start creating to add your first upload.</p>
                  @endforelse
                </div>
              </div>
              <div class="member-collection-tab-panel" id="memberCollectionOwnedPanel" role="tabpanel" aria-labelledby="memberCollectionTabOwned" hidden>
                <div class="member-owned-grid" id="memberCollectionItems">
                  @forelse ($ownedArtworks as $artwork)
                    <a class="member-art-tile" href="{{ route('week12.client.artwork', ['id' => $artwork->id]) }}">
                      <span class="member-art-tile-image"><img src="{{ asset(str_starts_with($artwork->image, 'storage/') ? $artwork->image : 'assets/' . $artwork->image) }}" alt="{{ $artwork->title }}" loading="lazy"></span>
                      <span class="member-art-tile-details"><strong>{{ $artwork->title }}</strong><small>Owned collection</small></span>
                    </a>
                  @empty
                    <p class="member-empty-state">Your collection is empty. Artwork purchases are disabled during beta.</p>
                  @endforelse
                </div>
              </div>
              <div class="member-collection-tab-panel" id="memberCollectionFavoritesPanel" role="tabpanel" aria-labelledby="memberCollectionTabFavorites" hidden>
                <div class="member-owned-grid" id="memberFavoriteItems">
                  @forelse ($likedArtworks as $artwork)
                    <a class="member-art-tile" href="{{ route('week12.client.artwork', ['id' => $artwork->id]) }}">
                      <span class="member-art-tile-image"><img src="{{ asset(str_starts_with($artwork->image, 'storage/') ? $artwork->image : 'assets/' . $artwork->image) }}" alt="{{ $artwork->title }}" loading="lazy"></span>
                      <span class="member-art-tile-details"><strong>{{ $artwork->title }}</strong><small>By {{ $artwork->creator }}</small><span>{{ number_format((float) $artwork->price, 2) }} ETH</span></span>
                    </a>
                  @empty
                    <p class="member-empty-state">You have not favorited any artwork yet. Use the heart on a listing to save it to your account.</p>
                  @endforelse
                </div>
              </div>
            </section>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="history" hidden>
            <div class="member-panel-heading"><div><h3>Purchases</h3><p>Account purchase order records. New purchases are disabled during beta.</p></div></div>
            <section class="member-panel-card member-transactions-card">
              <h4>Purchase orders</h4>
              <div class="member-history-table-wrap"><table class="member-history-table"><thead><tr><th>Purchase ID</th><th>Artwork</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead><tbody>
                @forelse ($purchaseOrders as $purchaseOrder)
                  <tr><td>{{ $purchaseOrder->purchase_id }}</td><td>{{ $purchaseOrder->artwork?->title ?? 'Artwork unavailable' }}</td><td>PHP {{ number_format($purchaseOrder->amount_minor / 100, 2) }}</td><td>{{ $purchaseOrder->payment_method }}</td><td>{{ str_replace('_', ' ', $purchaseOrder->status) }}</td></tr>
                @empty
                  <tr><td colspan="5" class="member-empty-state">No manual purchase orders yet.</td></tr>
                @endforelse
              </tbody></table></div>
            </section>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="earnings" hidden>
            <div class="member-panel-heading"><div><h3>Creator earnings</h3><p>Only approved purchase orders are included. New sales are disabled during beta.</p></div><span class="member-demo-label"><i class="bi bi-info-circle"></i> BETA</span></div>
            <article class="member-panel-card member-earnings-hero" data-earnings-total="{{ $creatorEarnings['totalCashMinor'] ?? 0 }}">
              <span class="member-earnings-icon"><i class="bi bi-stars"></i></span>
              <div>
                <small>APPROVED SALES</small>
                <strong>PHP {{ number_format(($creatorEarnings['totalCashMinor'] ?? 0) / 100, 2) }}</strong>
                <p>Recorded from approved orders attributed to you as seller.</p>
              </div>
            </article>
            <div class="member-stat-grid member-earnings-stats">
              <article class="member-stat-card"><span class="member-stat-icon"><i class="bi bi-bag-check"></i></span><div><small>Approved sales</small><strong>{{ number_format($creatorEarnings['salesCount'] ?? 0) }}</strong></div></article>
              <article class="member-stat-card"><span class="member-stat-icon"><i class="bi bi-calendar-check"></i></span><div><small>Approved sales, last 30 days</small><strong>PHP {{ number_format(($creatorEarnings['last30DaysCashMinor'] ?? 0) / 100, 2) }}</strong></div></article>
            </div>
            <div class="member-earnings-notice"><i class="bi bi-shield-check" aria-hidden="true"></i><p><strong>Beta status</strong> Sales and payouts are disabled. This page only displays historical approved order records.</p><a href="/user/pixel-coin">View balances <i class="bi bi-arrow-up-right"></i></a></div>
            <section class="member-panel-card member-transactions-card">
              <div class="member-panel-heading"><div><h4>Recent artwork sales</h4><p>Latest approved purchase orders for your listings.</p></div></div>
              <div class="member-history-table-wrap"><table class="member-history-table"><thead><tr><th>Artwork</th><th>Sale value</th><th>Date</th></tr></thead><tbody>
                @forelse (($creatorEarnings['recentSales'] ?? []) as $sale)
                  <tr><td>{{ $sale->title }}</td><td class="member-earnings-sale-amount">PHP {{ number_format((int) $sale->amount_minor / 100, 2) }}</td><td>{{ \Illuminate\Support\Carbon::parse($sale->created_at)->format('M j, Y') }}</td></tr>
                @empty
                  <tr><td colspan="3" class="member-empty-state">No approved sales for your artwork yet.</td></tr>
                @endforelse
              </tbody></table></div>
            </section>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="pixel-coin" hidden>
            <div class="member-panel-heading"><div><h3>Pixel Coin wallet</h3><p>Review your legacy balance and account history.</p></div><span class="member-demo-label"><i class="bi bi-info-circle"></i> DISABLED</span></div>
            <section class="member-panel-card member-pixel-wallet" aria-labelledby="memberPixelWalletHeading">
              <div class="member-wallet-overview">
                <span class="member-wallet-icon"><i class="bi bi-coin"></i></span>
                <div><small id="memberPixelWalletHeading">YOUR PIXEL COIN BALANCE</small><strong><span data-pixelcoin-balance>{{ number_format((int) ($account?->pixel_coin_balance ?? 0)) }}</span> <span>PIXEL COIN</span></strong><p>Pixel Coin is disabled and has no cash value.</p></div>
              </div>
              <div class="member-wallet-topups">
                <strong>Wallet actions unavailable</strong>
                <small>Top-ups and checkout are disabled until payment integration is available.</small>
                <p class="member-wallet-feedback" id="memberPixelWalletFeedback" role="status" aria-live="polite" hidden></p>
              </div>
            </section>
            <section class="member-panel-card member-transactions-card">
              <div class="member-panel-heading"><div><h4>Pixel Coin activity</h4><p>Legacy ledger records are read-only.</p></div></div>
              <div class="member-history-table-wrap"><table class="member-history-table"><thead><tr><th>Activity</th><th>Change</th><th>Balance after</th><th>Date</th></tr></thead><tbody id="memberPixelCoinActivity"><tr><td colspan="4" class="member-empty-state">Loading wallet activity…</td></tr></tbody></table></div>
            </section>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="resales" hidden>
            <div class="member-panel-heading"><div><h3>Resale artworks</h3><p>Resale actions are unavailable during beta.</p></div><span class="member-demo-label">DISABLED</span></div>
            @if (session('status'))<p role="status">{{ session('status') }}</p>@endif
            @if ($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
            @if (!config('payments.enabled'))
              <p class="member-payment-safety" role="status">Resale listings and reviews are disabled until payment processing is available.</p>
            @else
            @forelse ($ownedArtworks as $ownedArtwork)
              <article class="member-panel-card mb-3">
                <strong>{{ $ownedArtwork->title }}</strong>
                <p>Product ID: {{ $ownedArtwork->product_id }} · Resale status: {{ $ownedArtwork->resale_status ?? 'Not listed' }}</p>
                @if (!in_array($ownedArtwork->resale_status, ['pending_review', 'listed'], true))
                  <form method="post" action="{{ route('user.artworks.resale', $ownedArtwork->id) }}" enctype="multipart/form-data">
                    @csrf
                    <label>Resale price (PHP)<input name="price_php" type="number" min="1" max="10000000" required></label>
                    <label>Ownership/authorization evidence<input name="evidence" type="file" accept="image/png,image/jpeg,application/pdf" required></label>
                    <label class="member-policy-accept"><input name="ownership_declaration" type="checkbox" value="1" required> I own or am authorized to resell this artwork and the submission is accurate.</label>
                    <button class="member-profile-save" type="submit">Submit for review</button>
                  </form>
                @endif
              </article>
            @empty
              <p class="member-empty-state">Verified purchases will appear here when the payment is approved.</p>
            @endforelse
            @endif
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="payment" hidden>
            <div class="member-panel-heading"><div><h3>Purchases and payments</h3><p>Purchase and payment actions are unavailable in this beta.</p></div><span class="member-demo-label">DISABLED</span></div>
            <div class="member-payment-safety" role="status"><i class="bi bi-info-circle"></i><span>No payment provider is integrated. Do not send funds or submit payment information. This screen will be enabled after provider integration and operational review.</span></div>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="terms" hidden>
            <div class="member-panel-heading"><div><h3>Terms &amp; Originality Policy</h3><p>Marketplace rules for creators and collectors.</p></div><span class="member-demo-label">BETA</span></div>
            <article class="member-policy-content">
              <p class="member-policy-notice"><i class="bi bi-info-circle"></i> This student-project policy is an illustrative draft, not legal advice or a substitute for reviewed terms for a live marketplace.</p>
              <h4>1. Be truthful and respectful</h4><p>Use Pixmoree lawfully and provide accurate listing, creator, price, and transaction information. Do not impersonate another person or mislead collectors about ownership, authenticity, scarcity, or what a purchase includes.</p>
              <h4>2. Originality and anti-plagiarism</h4><p>Only upload work you created or have clear permission and appropriate licenses to use. Do not copy, trace, repackage, or mint another creator’s artwork, photographs, characters, logos, writing, or other protected material as your own. Credit alone does not replace permission.</p>
              <p>Do not use unauthorized AI training outputs, reference material, or third-party assets in a way that infringes another person’s rights. Keep source files and permission records where appropriate. If a work includes licensed or collaborative material, disclose that clearly and follow the license terms.</p>
              <h4>3. Reports, review, and consequences</h4><p>Rights holders should provide the listing link, identify the material at issue, and explain their rights when reporting suspected copying. Pixmoree should review reports, give creators a reasonable opportunity to respond where appropriate, and may hide or remove disputed listings, restrict accounts, or refer serious matters for further review. False or abusive reports are not permitted.</p>
              <h4>4. Payments and transactions</h4><p>Purchases, payments, payouts, resale, and Pixel Coin transactions are disabled during beta. Do not send funds or submit payment information. Artwork listings do not represent a completed sale, and no blockchain asset is minted or transferred.</p>
              <h4>5. Respect intellectual property</h4><p>Creators retain rights they hold in their work. A listing does not transfer copyright unless a separate, valid agreement says so. Collectors should not reproduce, sell, or commercially exploit artwork without the creator’s permission or a license that allows it.</p>
              <h4>6. Policy updates and contact</h4><p>These draft rules may change as the project evolves. For a real service, publish the effective date, operator identity, contact and appeal process, governing law, and privacy terms after professional review.</p>
            </article>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="profile" hidden>
            <div class="member-panel-heading"><div><h3>Profile Settings</h3><p>Your account information is saved securely to Pixmoree.</p></div><span class="member-demo-label">YOUR PROFILE</span></div>
            <a class="member-profile-save" href="/user/profile-settings">Manage profile details</a>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="notifications" hidden>
            <div class="member-panel-heading"><div><h3>Notifications</h3><p>In-app notifications are not enabled in the beta.</p></div></div>
            <p class="member-empty-state">Account and artwork updates are available from their respective pages.</p>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="about" hidden>
            <div class="member-about-page">
              @include('partials.about-content', ['aboutContent' => $aboutContent, 'galleryUrl' => '/user#explore'])
            </div>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="create" hidden>
            <div class="member-panel-heading"><div><h3>Create a listing</h3><p>Upload an original image and publish its listing to the Pixmoree database.</p></div><span class="member-demo-label">CREATOR STUDIO</span></div>
            <p><a class="profile-settings-secondary-button" href="{{ route('user.nft-management') }}"><i class="bi bi-list-ul" aria-hidden="true"></i> Manage your artwork listings</a></p>
            <form class="member-create-form" id="memberCreateForm" method="post" action="{{ route('user.artworks.store') }}" enctype="multipart/form-data">
              @csrf
              <label class="member-create-upload" for="memberArtworkFile"><i class="bi bi-cloud-arrow-up"></i><strong>Choose an artwork image</strong><span>PNG, JPG, or WEBP · up to 2 MB</span><img id="memberArtworkPreview" alt="Artwork preview" hidden></label>
              <input id="memberArtworkFile" name="image" type="file" accept="image/png,image/jpeg,image/webp" required>
              <label for="memberArtworkTitle">Artwork title<input id="memberArtworkTitle" name="title" type="text" maxlength="60" placeholder="e.g. Starlight Wanderer" required></label>
              <label for="memberArtworkDescription">Description<textarea id="memberArtworkDescription" name="description" rows="4" maxlength="500" placeholder="Tell collectors about your artwork." required></textarea></label>
              <div class="member-create-form-row"><label for="memberArtworkPrice">Price (ETH)<input id="memberArtworkPrice" name="price" type="number" min="0.01" max="100000" step="0.01" placeholder="0.25" required></label><label for="memberArtworkCategory">Category<select id="memberArtworkCategory" name="category"><option>Digital Art</option><option>Illustration</option><option>Photography</option><option>3D Art</option></select></label></div>
              <label for="memberArtworkItemCount">Items in this edition<input id="memberArtworkItemCount" name="item_count" type="number" min="1" max="100000" step="1" value="1" required></label>
              <label class="member-policy-accept"><input id="memberArtworkOriginality" name="originality" type="checkbox" required> I created this work or have permission to list it, and it does not knowingly violate anyone else’s rights. I agree to the <a href="/user/terms">Terms &amp; Originality Policy</a>.</label>
              <button class="member-profile-save" type="submit"><i class="bi bi-plus-lg"></i> Create listing</button>
              <p class="member-profile-feedback" id="memberCreateFeedback" role="status" hidden></p>
              @if ($errors->any())
                <p class="member-profile-feedback" role="alert">{{ $errors->first() }}</p>
              @endif
              @if (session('status'))
                <p class="member-profile-feedback" role="status">{{ session('status') }}</p>
              @endif
              <small>Your artwork details and image path are saved in the database, and the image file is stored securely. Duplicate image files are rejected.</small>
            </form>
            <section class="member-dashboard-panel mt-4" aria-labelledby="my-artworks-title">
              <h4 id="my-artworks-title">My artwork listings</h4>
              @forelse ($userArtworks as $listing)
                <details class="w12-panel mb-3">
                  <summary>{{ $listing->title }} · {{ $listing->status }}</summary>
                  <form method="post" action="{{ route('user.artworks.update', ['id' => $listing->id]) }}" enctype="multipart/form-data" class="member-create-form mt-3">
                    @csrf
                    @method('PATCH')
                    <label>Artwork title<input name="title" value="{{ $listing->title }}" maxlength="120" required></label>
                    <label>Description<textarea name="description" rows="3" maxlength="500" required>{{ $listing->description }}</textarea></label>
                    <div class="member-create-form-row">
                      <label>Category<input name="category" value="{{ $listing->category }}" maxlength="50" required></label>
                      <label>Price (ETH)<input name="price" type="number" min="0" max="99999999" step="0.01" value="{{ $listing->price }}" required></label>
                    </div>
                    <label>Items in this edition<input name="item_count" type="number" min="1" max="100000" step="1" value="{{ $listing->item_count }}" required></label>
                    <label>Replace image (optional)<input name="image" type="file" accept="image/png,image/jpeg,image/webp"></label>
                    <button class="member-profile-save" type="submit">Update artwork</button>
                  </form>
                  <form method="post" action="{{ route('user.artworks.destroy', ['id' => $listing->id]) }}" onsubmit="return confirm('Delete this artwork listing?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm mt-2" type="submit">Delete artwork</button>
                  </form>
                </details>
              @empty
                <p class="text-secondary">You have not listed any artwork yet.</p>
              @endforelse
            </section>
          </section>
        </div>
      </div>
    </section>
    <section id="trending" class="trending-nft-section">
      <div class="trending-nft-heading"><div><h2>{{ $homeContent['trending']['heading'] }}</h2><p>{{ $homeContent['trending']['description'] }}</p></div><a href="#explore" class="trending-view-all">{{ $homeContent['trending']['view_all'] }} <i class="bi bi-arrow-up-right"></i></a></div>
      <div class="trending-nft-grid">
        <article class="trending-feature" aria-label="Featured trending NFTs">
          <div class="trending-feature-slides" aria-live="polite">
            @foreach ($homeArtworks->take(5) as $artwork)
              <div class="trending-feature-slide {{ $loop->first ? 'active' : '' }}" data-artwork-id="{{ $artwork->id }}" data-title="{{ $artwork->title }}" data-creator="{{ $artwork->creator }}" data-floor="Ξ {{ number_format((float) $artwork->price, 2) }}" data-items="{{ $artwork->item_count }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                <img src="{{ asset(str_starts_with($artwork->image, 'storage/') ? $artwork->image : 'assets/' . $artwork->image) }}" alt="{{ $artwork->title }}">
              </div>
            @endforeach
          </div>
          @if ($homeArtworks->isNotEmpty())
            @php($featuredArtwork = $homeArtworks->first())
            <div class="trending-feature-info">
              <div><h3 class="trending-feature-title">{{ $featuredArtwork->title }} <i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p class="trending-feature-creator">By {{ $featuredArtwork->creator }}</p></div>
              <div class="trending-feature-stats"><div><small>Price</small><strong class="trending-feature-floor">Ξ {{ number_format((float) $featuredArtwork->price, 2) }}</strong></div><div><small>Items</small><strong class="trending-feature-items">1</strong></div></div>
            </div>
            @if ($homeArtworks->count() > 1)
              <button class="trending-feature-arrow trending-feature-prev" type="button" aria-label="Previous featured artwork"><i class="bi bi-chevron-left"></i></button>
              <button class="trending-feature-arrow trending-feature-next" type="button" aria-label="Next featured artwork"><i class="bi bi-chevron-right"></i></button>
              <div class="trending-nft-pagination" role="group" aria-label="Choose featured artwork">
                @foreach ($homeArtworks->take(5) as $artwork)
                  <button class="{{ $loop->first ? 'active' : '' }}" type="button" aria-label="Show featured artwork {{ $loop->iteration }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"></button>
                @endforeach
              </div>
            @endif
          @else
            <div class="trending-feature-info"><div><h3 class="trending-feature-title">{{ $homeContent['trending']['empty_heading'] }}</h3><p class="trending-feature-creator">{{ $homeContent['trending']['empty_body'] }}</p></div></div>
          @endif
        </article>
        @if ($homeArtworks->count() > 1)
          <div class="trending-nft-list" aria-label="Other listed artworks">
            @foreach ($homeArtworks->skip(1) as $artwork)
              <a class="trending-nft-item" data-artwork-id="{{ $artwork->id }}" href="{{ route('week12.client.artwork', ['id' => $artwork->id]) }}"><em class="trending-nft-rank">#{{ sprintf('%02d', $loop->iteration + 1) }}</em><img src="{{ asset(str_starts_with($artwork->image, 'storage/') ? $artwork->image : 'assets/' . $artwork->image) }}" alt="{{ $artwork->title }}"><span><strong>{{ $artwork->title }}</strong><small>By {{ $artwork->creator }}</small></span><b>Ξ {{ number_format((float) $artwork->price, 2) }}</b></a>
            @endforeach
          </div>
        @endif
      </div>
    </section>
    <section class="creator-strip-section">
      <div class="creator-strip-heading"><h2>{{ $homeContent['creators']['heading'] }}</h2><p>{{ $homeContent['creators']['description'] }}</p></div>
      <div class="creator-carousel" aria-label="Trending creators">
        <button class="creator-arrow creator-arrow-prev" type="button" aria-label="Scroll creators left"><i class="bi bi-chevron-left"></i></button>
        <div class="creator-track" id="creatorTrack">
          @forelse ($topCreators as $creator)
            <a class="creator-mini-card" href="{{ route('creators.show', ['creator' => $creator->id]) }}">
              <img class="creator-mini-avatar" src="{{ asset($creator->avatar_path ?? 'assets/Pixmoree Neon Pixel Logo.png') }}" alt="{{ $creator->name }}">
              <span class="creator-mini-copy"><strong>{{ $creator->name }}</strong><small>{{ $creator->artworks_count }} {{ \Illuminate\Support\Str::plural('artwork', $creator->artworks_count) }}</small></span>
              <small class="creator-mini-sales"><i class="bi bi-person-plus"></i> {{ number_format($creator->followers_count) }} followers</small>
            </a>
          @empty
            <p class="text-secondary">{{ $homeContent['creators']['empty_message'] }}</p>
          @endforelse
        </div>
        <button class="creator-arrow creator-arrow-next" type="button" aria-label="Scroll creators right"><i class="bi bi-chevron-right"></i></button>
      </div>
      <div class="creator-pagination" aria-hidden="true">
        @foreach ($topCreators->chunk(4) as $page)
          <span class="{{ $loop->first ? 'active' : '' }}"></span>
        @endforeach
      </div>
      <span id="creators" class="creator-anchor" aria-hidden="true"></span>
    </section>
  <section id="explore" class="explore-section">
    <div class="section-heading explore-heading"><div><h2>{{ $homeContent['explore']['heading'] }} <i class="bi bi-compass"></i></h2><p class="muted">{{ $homeContent['explore']['description'] }}</p></div></div>
    <div class="filter-row"><div class="filter-pills"><button class="filter-pill active" data-filter="all">{{ $homeContent['explore']['filters'][0] }}</button><button class="filter-pill" data-filter="newest">{{ $homeContent['explore']['filters'][1] }}</button><button class="filter-pill" data-filter="price">{{ $homeContent['explore']['filters'][2] }}</button><button class="filter-pill" data-filter="favorites">{{ $homeContent['explore']['filters'][3] }}</button></div><label class="sort-button"><i class="bi bi-sort-down"></i><span>{{ $homeContent['explore']['sort_label'] }}</span><select id="sortSelect" aria-label="Sort artworks"><option value="default">Default</option><option value="price-low">Price: low to high</option><option value="price-high">Price: high to low</option></select></label></div>
    <div class="row g-3" id="artGrid">
      @forelse ($homeArtworks as $artwork)
        <div class="col-6 col-sm-3 art-column" data-artwork-id="{{ $artwork->id }}" data-category="{{ \Illuminate\Support\Str::slug($artwork->category) }}" data-price="{{ $artwork->price }}" data-created="{{ $artwork->created_at?->timestamp ?? 0 }}">
          <article class="art-card">
            <div class="art-image-wrap">
              <img src="{{ asset(str_starts_with($artwork->image, 'storage/') ? $artwork->image : 'assets/' . $artwork->image) }}" alt="{{ $artwork->title }} digital artwork">
              <span class="artwork-title">{{ $artwork->title }}</span>
              @auth
                @php($artworkLiked = in_array($artwork->id, $likedArtworkIds, true))
                <button class="heart-button art-engagement-like" type="button" data-engagement-like="artwork" data-entity-id="{{ $artwork->id }}" data-liked="{{ $artworkLiked ? 'true' : 'false' }}" aria-pressed="{{ $artworkLiked ? 'true' : 'false' }}" aria-label="{{ $artworkLiked ? 'Remove' : 'Add' }} {{ $artwork->title }} {{ $artworkLiked ? 'from' : 'to' }} favorites"><i class="bi {{ $artworkLiked ? 'bi-heart-fill' : 'bi-heart' }}" aria-hidden="true"></i></button>
              @else
                <a class="heart-button guest-heart" href="{{ route('login') }}" aria-label="Log in to favorite {{ $artwork->title }}"><i class="bi bi-heart" aria-hidden="true"></i></a>
              @endauth
            </div>
            <div class="art-card-body">
              <div class="art-creator">By <span>{{ $artwork->creator }}</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div>
              <div class="art-stats"><div class="art-price"><small>Asking price</small><strong>{{ number_format((float) $artwork->price, 2) }} ETH</strong></div><div class="art-items"><small>Items</small><strong>{{ number_format($artwork->item_count) }}</strong></div></div>
              <div class="art-engagement" data-engagement-metrics="artwork" data-entity-id="{{ $artwork->id }}">
                <span><i class="bi bi-eye" aria-hidden="true"></i> <span data-engagement-views>{{ number_format($artwork->views_count) }}</span></span>
                @auth
                  @php($artworkLiked = in_array($artwork->id, $likedArtworkIds, true))
                  <button class="art-engagement-like" type="button" data-engagement-like="artwork" data-entity-id="{{ $artwork->id }}" data-liked="{{ $artworkLiked ? 'true' : 'false' }}" aria-pressed="{{ $artworkLiked ? 'true' : 'false' }}"><i class="bi {{ $artworkLiked ? 'bi-heart-fill' : 'bi-heart' }}" aria-hidden="true"></i> <span data-engagement-likes>{{ number_format($artwork->likes_count) }}</span></button>
                @else
                  <a class="art-engagement-like" href="{{ route('login') }}"><i class="bi bi-heart" aria-hidden="true"></i> <span data-engagement-likes>{{ number_format($artwork->likes_count) }}</span></a>
                @endauth
              </div>
            </div>
          </article>
        </div>
      @empty
        <p class="text-secondary">{{ $homeContent['explore']['empty_message'] }}</p>
      @endforelse
    </div>
    <div class="load-more-wrap"><button class="btn-load" id="loadMoreItems" type="button"><i class="bi bi-plus-circle" aria-hidden="true"></i> {{ $homeContent['explore']['load_more'] }}</button></div>
  </section>
  <section id="about" class="guest-about"><h2>{{ $homeContent['closing_heading'] }}</h2><p>{{ $homeContent['closing_body'] }}</p><a href="/login" class="btn hero-explore">{{ $homeContent['closing_cta'] }}</a></section>
  <footer class="site-footer"><a href="#"><img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree" class="footer-logo"></a><p>{{ $homeContent['footer_tagline'] }}</p><div class="footer-links"><a href="#explore">{{ $homeContent['footer_links'][0] }}</a><a href="#creators">{{ $homeContent['footer_links'][1] }}</a><a href="#">{{ $homeContent['footer_links'][2] }}</a><a href="#">{{ $homeContent['footer_links'][3] }}</a></div><small>© {{ date('Y') }} Pixmoree. {{ $homeContent['footer_note'] }}</small></footer>
</main>
<dialog class="nft-preview" id="nftPreview" aria-labelledby="nftPreviewTitle">
  <div class="nft-preview-card">
    <button class="nft-preview-close" type="button" aria-label="Close NFT preview"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    <div class="nft-preview-art"><img id="nftPreviewImage" alt=""></div>
    <div class="nft-preview-details">
      <p class="nft-preview-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> Digital collectible</p>
      <h2 id="nftPreviewTitle"></h2>
      <p class="nft-preview-creator" id="nftPreviewCreator"></p>
      <div class="nft-preview-engagement" id="nftPreviewEngagement" data-engagement-metrics="artwork" data-entity-id="">
        <span><i class="bi bi-eye" aria-hidden="true"></i> <span data-engagement-views>0</span> views</span>
        @auth
          <button type="button" data-engagement-like="artwork" data-entity-id="" data-liked="false" aria-pressed="false"><i class="bi bi-heart" aria-hidden="true"></i> <span data-engagement-label>Like</span> <span data-engagement-likes>0</span></button>
        @else
          <a href="{{ route('login') }}"><i class="bi bi-heart" aria-hidden="true"></i> Log in to like <span data-engagement-likes>0</span></a>
        @endauth
      </div>
      <dl class="nft-preview-stats">
        <div id="nftPreviewRankRow" hidden><dt>Trending rank</dt><dd id="nftPreviewRank"></dd></div>
        <div id="nftPreviewPriceRow" hidden><dt>Floor price</dt><dd id="nftPreviewPrice"></dd></div>
        <div id="nftPreviewItemsRow" hidden><dt>Items</dt><dd id="nftPreviewItems"></dd></div>
      </dl>
      <a class="nft-preview-explore" href="#explore">Explore gallery <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        <a class="nft-preview-full-details" id="nftPreviewFullDetails" href="{{ $account ? route('user').'#explore' : '/#explore' }}"><i class="bi bi-box-arrow-up-right"></i> View full details</a>
    </div>
  </div>
</dialog>
<script>window.pixmoreeAccount = <?= json_encode($account?->only(['name', 'username', 'email', 'phone', 'bio', 'pixel_coin_balance']) ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/site.js') }}?v=20261030-me"></script>
<script src="{{ asset('js/engagement.js') }}?v=20261104-engagement"></script>
</body>
</html>
