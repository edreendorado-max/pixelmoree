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
    <link href="{{ asset('css/site.css') }}?v=20261029-earnings" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark site-nav guest-nav">
  <div class="container-fluid px-3 px-lg-5">
    <a class="navbar-brand" href="#home" aria-label="Pixmoree home"><img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree" class="brand-logo"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="mainNav">
      <div class="guest-links mx-lg-auto my-3 my-lg-0">
        <a class="member-create-link member-only-nav-link" href="/user/create" data-member-nav="create" hidden><i class="bi bi-plus-lg"></i><span>Create</span></a>
        <a class="active" href="#home" data-member-nav="home"><i class="bi bi-house-door"></i><span>Home</span></a>
        <a href="#explore" data-member-nav="collection"><i class="bi bi-images"></i><span>Gallery</span></a>
        <a class="guest-only-nav-link" href="#trending" data-member-nav="trending"><i class="bi bi-graph-up-arrow"></i><span>Trending</span></a>
        <a class="guest-only-nav-link" href="/about"><i class="bi bi-info-circle"></i><span>About</span></a>
        <a href="/user/dashboard" class="member-only-nav-link" data-member-nav="dashboard" hidden><i class="bi bi-grid"></i><span>Dashboard</span></a>
        <a href="/user/purchases" class="member-only-nav-link" data-member-nav="history" hidden><i class="bi bi-receipt"></i><span>Purchases</span></a>
        <a href="/user/earnings" class="member-only-nav-link" data-member-nav="earnings" hidden><i class="bi bi-graph-up-arrow"></i><span>Earnings</span></a>
        <a href="/user/terms" class="member-only-nav-link" data-member-nav="terms" hidden><i class="bi bi-shield-check"></i><span>Terms &amp; Originality</span></a>
        <a href="/user/notifications" class="member-only-nav-link" data-member-nav="notifications" hidden><i class="bi bi-bell"></i><span>Notifications <small class="member-nav-badge" id="memberNotificationBadge" hidden>0</small></span></a>
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
        <a href="/user/notifications" class="member-notification-button member-only-nav-link" aria-label="Notifications" hidden><i class="bi bi-bell"></i><span id="memberTopNotificationBadge" hidden>0</span></a>
        <div class="member-profile" id="memberProfile" hidden>
          <button class="member-profile-toggle" id="memberProfileToggle" type="button" aria-expanded="false" aria-controls="memberProfileMenu"><span class="member-avatar" id="memberNavAvatar"><span class="member-avatar-initial">{{ strtoupper(substr($account?->name ?? 'A', 0, 1)) }}</span></span><span id="memberNavName">{{ $account?->name ?? 'Account' }}</span><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
          <div class="member-profile-menu" id="memberProfileMenu" hidden>
            <span class="member-menu-heading">SIGNED IN AS {{ strtoupper($account?->name ?? 'ACCOUNT') }}</span>
            <a href="/user" data-member-action="dashboard"><i class="bi bi-house"></i> Home</a>
            <a href="/user/collection" data-member-action="collection"><i class="bi bi-images"></i> My Collection <span id="memberMenuCollectionCount">0</span></a>
            <a href="/user/dashboard" data-member-action="dashboard"><i class="bi bi-grid"></i> Dashboard</a>
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
<main class="nft-detail-page" id="nftDetailPage" hidden>
  <a class="nft-detail-back" id="nftDetailBack" href="/#explore"><i class="bi bi-arrow-left"></i> Back to gallery</a>
  <div class="nft-detail-layout" id="nftDetailLayout" hidden>
    <div class="nft-detail-art"><img id="nftDetailImage" alt=""></div>
    <article class="nft-detail-content">
      <p class="nft-detail-kicker"><i class="bi bi-stars"></i> DIGITAL COLLECTIBLE <span id="nftDetailRank" hidden></span></p>
      <h1 id="nftDetailTitle"></h1>
      <p class="nft-detail-creator">Created by <strong id="nftDetailCreator"></strong></p>
      <p class="nft-detail-description" id="nftDetailDescription"></p>
      <div class="nft-detail-price"><small>Current asking price <span>(demo)</span></small><strong id="nftDetailPrice"></strong></div>
      <div class="nft-detail-estimate"><small>Estimated market value <span>(demo estimate)</span></small><strong id="nftDetailMarketValue"></strong><small id="nftDetailMarketChange"></small></div>
      <div class="nft-detail-actions">
        <button class="nft-detail-buy" id="nftDetailBuy" type="button" hidden><i class="bi bi-bag-plus"></i> Buy NFT (demo)</button>
        <button class="nft-detail-favorite" id="nftDetailFavorite" type="button" hidden><i class="bi bi-heart"></i> <span>Favorite</span></button>
      </div>
      <p class="nft-detail-feedback" id="nftDetailFeedback" role="status" hidden></p>
      <section class="nft-detail-panel" aria-labelledby="nftDetailInfoHeading">
        <h2 id="nftDetailInfoHeading">NFT information</h2>
        <dl class="nft-detail-info">
          <div><dt>Collection</dt><dd id="nftDetailCollection"></dd></div>
          <div><dt>Category</dt><dd id="nftDetailCategory"></dd></div>
          <div><dt>Token ID</dt><dd id="nftDetailToken"></dd></div>
          <div><dt>Blockchain</dt><dd>Ethereum <small>Demo</small></dd></div>
          <div><dt>Token standard</dt><dd>ERC-721 <small>Demo</small></dd></div>
          <div><dt>Collection items</dt><dd id="nftDetailItems"></dd></div>
          <div><dt>Creator royalty</dt><dd>5% <small>Sample</small></dd></div>
        </dl>
      </section>
      <p class="nft-detail-disclaimer">Marketplace metadata, blockchain, and transaction details on this page are illustrative demo information only.</p>
    </article>
  </div>
  <div class="nft-detail-error" id="nftDetailError" hidden>
    <i class="bi bi-exclamation-circle"></i>
    <h1>NFT details are unavailable</h1>
    <p>This demo listing could not be found in this browser. Open the artwork from the Pixmoree gallery to view its details.</p>
    <a id="nftDetailErrorLink" href="/#explore">Return to the gallery</a>
  </div>
</main>
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
            <div class="member-home-heading"><div><span class="member-welcome-kicker"><i class="bi bi-stars"></i> YOUR CREATIVE SPACE</span><h1>Make room for imagination.</h1><p>Explore art, track your collection, and share something of your own.</p></div></div>
            <div class="member-stat-grid member-home-stat-grid">
              <article class="member-stat-card"><span class="member-stat-icon"><i class="bi bi-images"></i></span><div><small>My Collection</small><strong id="memberHomeOwnedCount">0</strong></div><a href="/user/collection" aria-label="Open My Collection"><i class="bi bi-arrow-up-right"></i></a></article>
              <article class="member-stat-card"><span class="member-stat-icon"><i class="bi bi-receipt"></i></span><div><small>Purchases</small><strong id="memberHomePurchaseCount">0</strong></div><a href="/user/purchases" aria-label="Open purchases"><i class="bi bi-arrow-up-right"></i></a></article>
              <article class="member-stat-card"><span class="member-stat-icon"><i class="bi bi-heart-fill"></i></span><div><small>Favorites</small><strong id="memberHomeFavoriteCount">0</strong></div><a href="/user/favorites" aria-label="Open favorites"><i class="bi bi-arrow-up-right"></i></a></article>
            </div>
            <div class="member-panel-card"><div class="member-panel-heading"><div><h3>Recently viewed</h3><p>Your latest discoveries.</p></div><a href="/user#explore">Explore <i class="bi bi-arrow-up-right"></i></a></div><div class="member-recent-grid" id="memberRecentItems"><p class="member-empty-state">Artwork you view will show up here.</p></div></div>
          </section>
          <section class="member-dashboard-panel active" data-member-panel-content="dashboard">
            <div class="member-panel-heading"><div><h3>Dashboard</h3><p>Your account at a glance.</p></div><span class="member-demo-label">YOUR ACCOUNT</span></div>
            <article class="member-dashboard-profile-card">
              @if ($account?->banner_path)
                <img class="member-dashboard-banner" id="memberDashboardBanner" src="{{ asset($account->banner_path) }}" alt="">
              @else
                <img class="member-dashboard-banner" id="memberDashboardBanner" alt="" hidden>
              @endif
              <span class="member-dashboard-avatar" id="memberDashboardAvatar"><span class="member-avatar-initial">{{ strtoupper(substr($account?->name ?? 'A', 0, 1)) }}</span><img id="memberDashboardAvatarImage" alt="" hidden></span>
              <div class="member-dashboard-identity"><small>YOUR PROFILE</small><strong id="memberDashboardProfileName">{{ $account?->name ?? 'Account' }}</strong><span id="memberDashboardUsername">{{ '@'.strtolower(str_replace(' ', '-', $account?->name ?? 'account')) }}</span></div>
              <div class="member-dashboard-profile-stats">
                <div><small>Profile likes</small><strong id="memberDashboardLikes">0</strong></div>
                <div><small>Items sold</small><strong id="memberDashboardSold">0</strong></div>
              </div>
            </article>
            <div class="member-stat-grid">
              <article class="member-stat-card"><span class="member-stat-icon"><i class="bi bi-heart-fill"></i></span><div><small>Favorite artwork</small><strong id="memberWelcomeFavorites">0</strong></div><button type="button" data-member-panel-link="favorites" aria-label="Open favorites"><i class="bi bi-arrow-up-right"></i></button></article>
              <article class="member-stat-card"><span class="member-stat-icon"><i class="bi bi-images"></i></span><div><small>Owned NFTs</small><strong id="memberOwnedCount">0</strong></div><button type="button" data-member-panel-link="collection" aria-label="Open collection"><i class="bi bi-arrow-up-right"></i></button></article>
              <article class="member-stat-card"><span class="member-stat-icon"><i class="bi bi-receipt"></i></span><div><small>Purchases</small><strong id="memberPurchaseCount">0</strong></div><button type="button" data-member-panel-link="history" aria-label="Open purchase history"><i class="bi bi-arrow-up-right"></i></button></article>
            </div>
            <div class="member-panel-card member-dashboard-gallery-card">
              <div class="member-panel-heading"><div><h3>Gallery</h3><p>Your uploads and collected artwork in one place.</p></div><a href="/user/create" class="member-dashboard-create"><i class="bi bi-plus-lg"></i> Create</a></div>
              <div class="member-owned-grid" id="memberDashboardGallery"><p class="member-empty-state">Your gallery is empty. Create or collect artwork to see it here.</p></div>
            </div>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="collection" hidden>
            <div class="member-panel-heading"><div><h3>My Collection</h3><p>Manage artwork you uploaded, own, or saved as a favorite.</p></div></div>
            <div class="member-collection-tabs" role="tablist" aria-label="My Collection sections">
              <button type="button" role="tab" id="memberCollectionTabUploads" data-collection-tab="uploads" aria-controls="memberCollectionUploadsPanel">My Uploads</button>
              <button type="button" role="tab" id="memberCollectionTabOwned" data-collection-tab="owned" aria-controls="memberCollectionOwnedPanel">Owned <span id="memberCollectionCount">0</span></button>
              <button type="button" role="tab" id="memberCollectionTabFavorites" data-collection-tab="favorites" aria-controls="memberCollectionFavoritesPanel">My Favorites <span id="memberFavoritePanelCount">0</span></button>
            </div>
            <div class="member-collection-tab-panel" id="memberCollectionUploadsPanel" role="tabpanel" aria-labelledby="memberCollectionTabUploads" hidden>
              <div class="member-owned-grid" id="memberCollectionUploads"><p class="member-empty-state">You have not uploaded any artwork yet. Start creating to add your first upload.</p></div>
            </div>
            <div class="member-collection-tab-panel" id="memberCollectionOwnedPanel" role="tabpanel" aria-labelledby="memberCollectionTabOwned" hidden>
              <div class="member-owned-grid" id="memberCollectionItems"><p class="member-empty-state">Your collection is empty. Explore NFTs and add a demo purchase.</p></div>
            </div>
            <div class="member-collection-tab-panel" id="memberCollectionFavoritesPanel" role="tabpanel" aria-labelledby="memberCollectionTabFavorites" hidden>
              <div class="member-owned-grid" id="memberFavoriteItems"><p class="member-empty-state">No favorites yet. Save artwork with the heart button while exploring.</p></div>
            </div>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="history" hidden>
            <div class="member-panel-heading"><div><h3>Purchases</h3><p>Demo transaction records; no real payments are processed.</p></div></div>
            <div class="member-history-table-wrap"><table class="member-history-table"><thead><tr><th>Artwork</th><th>Price paid</th><th>Date</th><th>Status</th></tr></thead><tbody id="memberHistoryItems"><tr><td colspan="4" class="member-empty-state">No demo purchases yet.</td></tr></tbody></table></div>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="earnings" hidden>
            <div class="member-panel-heading"><div><h3>Earnings balance</h3><p>Track demo Pixel Coin purchases of your artwork.</p></div><span class="member-demo-label"><i class="bi bi-info-circle"></i> DEMO SUMMARY</span></div>
            <article class="member-panel-card member-earnings-hero" data-earnings-total="{{ $creatorEarnings['totalPixelCoins'] ?? 0 }}">
              <span class="member-earnings-icon"><i class="bi bi-stars"></i></span>
              <div>
                <small>ESTIMATED DEMO EARNINGS</small>
                <strong>{{ number_format($creatorEarnings['totalPixelCoins'] ?? 0) }} <span>PIXEL COIN</span></strong>
                <p>Purchase value recorded for artwork you created.</p>
              </div>
            </article>
            <div class="member-stat-grid member-earnings-stats">
              <article class="member-stat-card"><span class="member-stat-icon"><i class="bi bi-bag-check"></i></span><div><small>Completed demo sales</small><strong>{{ number_format($creatorEarnings['salesCount'] ?? 0) }}</strong></div></article>
              <article class="member-stat-card"><span class="member-stat-icon"><i class="bi bi-calendar-check"></i></span><div><small>Demo sales, last 30 days</small><strong>{{ number_format($creatorEarnings['last30DaysPixelCoins'] ?? 0) }} <small>PIXEL</small></strong></div></article>
            </div>
            <div class="member-earnings-notice"><i class="bi bi-shield-check" aria-hidden="true"></i><p><strong>Demo tracking only</strong> This total is calculated from recorded Pixel Coin purchases. It is not credited to your wallet, has no cash value, and cannot be withdrawn. No real payments are processed.</p><a href="/user/pixel-coin">View wallet <i class="bi bi-arrow-up-right"></i></a></div>
            <section class="member-panel-card member-transactions-card">
              <div class="member-panel-heading"><div><h4>Recent artwork sales</h4><p>Latest recorded demo purchases of your artwork.</p></div></div>
              <div class="member-history-table-wrap"><table class="member-history-table"><thead><tr><th>Artwork</th><th>Demo sale value</th><th>Date</th></tr></thead><tbody>
                @forelse (($creatorEarnings['recentSales'] ?? []) as $sale)
                  <tr><td>{{ $sale->title }}</td><td class="member-earnings-sale-amount">+{{ number_format((int) $sale->amount) }} PIXEL</td><td>{{ \Illuminate\Support\Carbon::parse($sale->created_at)->format('M j, Y') }}</td></tr>
                @empty
                  <tr><td colspan="3" class="member-empty-state">No demo sales for your artwork yet. When a signed-in user records a Pixel Coin demo purchase, it will appear here.</td></tr>
                @endforelse
              </tbody></table></div>
            </section>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="pixel-coin" hidden>
            <div class="member-panel-heading"><div><h3>Pixel Coin wallet</h3><p>Your demo balance and recent account activity are saved to Pixmoree.</p></div><span class="member-demo-label"><i class="bi bi-info-circle"></i> DEMO CREDITS ONLY</span></div>
            <section class="member-panel-card member-pixel-wallet" aria-labelledby="memberPixelWalletHeading">
              <div class="member-wallet-overview">
                <span class="member-wallet-icon"><i class="bi bi-coin"></i></span>
                <div><small id="memberPixelWalletHeading">YOUR PIXEL COIN BALANCE</small><strong><span data-pixelcoin-balance>{{ number_format((int) ($account?->pixel_coin_balance ?? 0)) }}</span> <span>PIXEL COIN</span></strong><p>Free demo credits · no cash value</p></div>
              </div>
              <div class="member-wallet-topups">
                <strong>Add free demo credits</strong>
                <div class="member-wallet-topup-options">
                  <button type="button" data-pixel-topup="500">+500 <small>PIXEL COIN</small></button>
                  <button type="button" data-pixel-topup="1000">+1,000 <small>PIXEL COIN</small></button>
                  <button type="button" data-pixel-topup="2500">+2,500 <small>PIXEL COIN</small></button>
                </div>
                <small>These server-recorded demo credits are free, non-cash, and cannot be purchased or redeemed. No payment provider is connected.</small>
                <p class="member-wallet-feedback" id="memberPixelWalletFeedback" role="status" aria-live="polite" hidden></p>
              </div>
            </section>
            <section class="member-panel-card member-transactions-card">
              <div class="member-panel-heading"><div><h4>Pixel Coin activity</h4><p>Recent demo credits and Pixel Coin checkout deductions.</p></div></div>
              <div class="member-history-table-wrap"><table class="member-history-table"><thead><tr><th>Activity</th><th>Change</th><th>Balance after</th><th>Date</th></tr></thead><tbody id="memberPixelCoinActivity"><tr><td colspan="4" class="member-empty-state">Loading wallet activity…</td></tr></tbody></table></div>
            </section>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="payment" hidden>
            <div class="member-payment-heading"><div><span class="member-welcome-kicker"><i class="bi bi-shield-check"></i> PIXMOREE CHECKOUT</span><h3>Payment &amp; Transactions</h3><p>Review your order and choose a demo payment method.</p></div><span class="member-demo-label"><i class="bi bi-info-circle"></i> SIMULATION ONLY</span></div>
            <div class="member-payment-safety"><i class="bi bi-shield-lock"></i><span><strong>Safe demo checkout</strong> Payment providers are not connected. This page never asks for card/account numbers, passwords, PINs, or OTPs; no payment is sent or charged. Never share an OTP or wallet PIN in chat or with a seller.</span></div>
            <section class="member-panel-card member-pixel-wallet" aria-labelledby="memberPaymentWalletHeading">
              <div class="member-wallet-overview"><span class="member-wallet-icon"><i class="bi bi-coin"></i></span><div><small id="memberPaymentWalletHeading">PIXMOREE DEMO WALLET</small><strong><span data-pixelcoin-balance>{{ number_format((int) ($account?->pixel_coin_balance ?? 0)) }}</span> <span>PIXEL COIN</span></strong><p>Demo-only credits · no cash value</p></div></div>
              <a class="member-wallet-topup-link" href="/user/pixel-coin"><i class="bi bi-plus-circle"></i> Manage wallet and add free demo credits</a>
            </section>
            <div class="member-payment-layout">
              <section class="member-panel-card member-checkout-card" aria-labelledby="memberCheckoutHeading">
                <div class="member-payment-card-heading"><span class="member-payment-step">1</span><div><h4 id="memberCheckoutHeading">Order summary</h4><p>Review the NFT you selected before confirming.</p></div></div>
                <div id="memberCheckoutArtwork" class="member-checkout-artwork" aria-live="polite" hidden></div>
                <dl class="member-payment-totals">
                  <div><dt>Item price</dt><dd id="memberCheckoutPrice">—</dd></div>
                  <div><dt>Payment processing</dt><dd>No charge in demo</dd></div>
                  <div class="member-payment-total"><dt>Total due</dt><dd id="memberCheckoutTotal">—</dd></div>
                </dl>
                <p class="member-pixel-rate" id="memberPixelRateNote" hidden>Demo estimate: 1 ETH = 1,000 PIXEL COIN. This is a fixed in-app demo rate and is not a market exchange rate.</p>
                <p class="member-pixel-insufficient" id="memberPixelInsufficient" role="status" hidden></p>
                <small class="member-payment-help">The listed ETH amount is illustrative. No fiat conversion, network fee, or live quote is calculated.</small>
              </section>
              <section class="member-panel-card member-checkout-card" aria-labelledby="memberPaymentHeading">
                <div class="member-payment-card-heading"><span class="member-payment-step">2</span><div><h4 id="memberPaymentHeading">Payment method</h4><p>Choose a provider to record in this demo receipt.</p></div></div>
                <form id="memberPaymentForm" class="member-payment-form">
                  <label class="member-payment-field" for="memberPaymentMethod">Available demo methods<select id="memberPaymentMethod" name="paymentMethod"><option value="Pixel Coin (Demo)">Pixel Coin wallet</option><option value="GCash (Demo)">GCash</option><option value="Maya (Demo)">Maya</option><option value="MariBank (Demo)">MariBank</option><option value="Bank transfer (Demo)">Bank transfer</option><option value="Visa / Mastercard (Demo)">Visa / Mastercard</option><option value="Other e-wallet (Demo)">Other e-wallet</option><option value="Demo wallet">Demo wallet</option></select></label>
                  <p class="member-payment-method-note"><i class="bi bi-info-circle"></i> Provider selection is for demonstration only. You will not be redirected to a provider or asked to enter payment credentials.</p>
                  <label class="member-policy-accept"><input id="memberPaymentTerms" type="checkbox" required> I have reviewed this demo order and agree to the <a href="/user/terms">demo terms</a>.</label>
                  <button class="member-profile-save" id="memberPaymentSubmit" type="submit" disabled><i class="bi bi-lock"></i> Review order to continue</button>
                  <p id="memberPaymentFeedback" class="member-profile-feedback" role="status" aria-live="polite" hidden></p>
                </form>
              </section>
            </div>
            <section class="member-panel-card member-transactions-card">
              <div class="member-panel-heading"><div><h4>Transaction history</h4><p>Local demo receipts saved in this browser. Do not use these as proof of a real payment.</p></div></div>
              <div class="member-history-table-wrap"><table class="member-history-table"><thead><tr><th>Demo reference</th><th>Artwork</th><th>Amount</th><th>Method</th><th>Date</th><th>Status</th></tr></thead><tbody id="memberTransactionItems"><tr><td colspan="6" class="member-empty-state">No demo transactions yet.</td></tr></tbody></table></div>
            </section>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="terms" hidden>
            <div class="member-panel-heading"><div><h3>Terms &amp; Originality Policy</h3><p>Marketplace rules for creators and collectors.</p></div><span class="member-demo-label">DRAFT FOR DEMO</span></div>
            <article class="member-policy-content">
              <p class="member-policy-notice"><i class="bi bi-info-circle"></i> This student-project policy is an illustrative draft, not legal advice or a substitute for reviewed terms for a live marketplace.</p>
              <h4>1. Be truthful and respectful</h4><p>Use Pixmoree lawfully and provide accurate listing, creator, price, and transaction information. Do not impersonate another person or mislead collectors about ownership, authenticity, scarcity, or what a purchase includes.</p>
              <h4>2. Originality and anti-plagiarism</h4><p>Only upload work you created or have clear permission and appropriate licenses to use. Do not copy, trace, repackage, or mint another creator’s artwork, photographs, characters, logos, writing, or other protected material as your own. Credit alone does not replace permission.</p>
              <p>Do not use unauthorized AI training outputs, reference material, or third-party assets in a way that infringes another person’s rights. Keep source files and permission records where appropriate. If a work includes licensed or collaborative material, disclose that clearly and follow the license terms.</p>
              <h4>3. Reports, review, and consequences</h4><p>Rights holders should provide the listing link, identify the material at issue, and explain their rights when reporting suspected copying. Pixmoree should review reports, give creators a reasonable opportunity to respond where appropriate, and may hide or remove disputed listings, restrict accounts, or refer serious matters for further review. False or abusive reports are not permitted.</p>
              <h4>4. Purchases and demo transactions</h4><p>Pixel Coin balances and demo coin events are stored in the account database. Demo purchase receipts are saved in this browser. The service does not process payments, create blockchain tokens, transfer ownership, or provide refunds. A production marketplace would need clear sale, refund, privacy, tax, and dispute terms before launch.</p>
              <h4>5. Respect intellectual property</h4><p>Creators retain rights they hold in their work. A listing or demo purchase does not transfer copyright unless a separate, valid agreement says so. Collectors should not reproduce, sell, or commercially exploit artwork without the creator’s permission or a license that allows it.</p>
              <h4>6. Policy updates and contact</h4><p>These draft rules may change as the project evolves. For a real service, publish the effective date, operator identity, contact and appeal process, governing law, and privacy terms after professional review.</p>
            </article>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="profile" hidden>
            <div class="member-panel-heading"><div><h3>Profile Settings</h3><p>Your account information is saved securely to Pixmoree.</p></div><span class="member-demo-label">YOUR PROFILE</span></div>
            <a class="member-profile-save" href="/user/profile-settings">Manage profile details</a>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="notifications" hidden>
            <div class="member-panel-heading"><div><h3>Notifications</h3><p>Recent activity from your collection and creator space.</p></div><button id="memberMarkNotificationsRead" class="member-notifications-read" type="button">Mark all as read</button></div>
            <div id="memberNotificationsList" class="member-notification-list"></div>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="about" hidden>
            <div class="member-about-page">
              @include('partials.about-content', ['aboutContent' => $aboutContent, 'galleryUrl' => '/user#explore'])
            </div>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="create" hidden>
            <div class="member-panel-heading"><div><h3>Create a listing</h3><p>Upload an original image and publish its listing to the Pixmoree database.</p></div><span class="member-demo-label">CREATOR STUDIO</span></div>
            <form class="member-create-form" id="memberCreateForm" method="post" action="{{ route('user.artworks.store') }}" enctype="multipart/form-data">
              @csrf
              <label class="member-create-upload" for="memberArtworkFile"><i class="bi bi-cloud-arrow-up"></i><strong>Choose an artwork image</strong><span>PNG, JPG, or WEBP · up to 2 MB</span><img id="memberArtworkPreview" alt="Artwork preview" hidden></label>
              <input id="memberArtworkFile" name="image" type="file" accept="image/png,image/jpeg,image/webp" required>
              <label for="memberArtworkTitle">Artwork title<input id="memberArtworkTitle" name="title" type="text" maxlength="60" placeholder="e.g. Starlight Wanderer" required></label>
              <label for="memberArtworkDescription">Description<textarea id="memberArtworkDescription" name="description" rows="4" maxlength="500" placeholder="Tell collectors about your artwork." required></textarea></label>
              <div class="member-create-form-row"><label for="memberArtworkPrice">Price (ETH)<input id="memberArtworkPrice" name="price" type="number" min="0.01" max="100000" step="0.01" placeholder="0.25" required></label><label for="memberArtworkCategory">Category<select id="memberArtworkCategory" name="category"><option>Digital Art</option><option>Illustration</option><option>Photography</option><option>3D Art</option></select></label></div>
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
              @php($marketEstimate = $artwork->marketEstimate())
              <div class="trending-feature-slide {{ $loop->first ? 'active' : '' }}" data-artwork-id="{{ $artwork->id }}" data-title="{{ $artwork->title }}" data-creator="{{ $artwork->creator }}" data-floor="Ξ {{ number_format((float) $artwork->price, 2) }}" data-market-value="{{ number_format($marketEstimate['value'], 2, '.', '') }}" data-market-change="{{ number_format($marketEstimate['change_percent'], 1, '.', '') }}" data-items="1" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
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
              @php($marketEstimate = $artwork->marketEstimate())
              <a class="trending-nft-item" data-artwork-id="{{ $artwork->id }}" data-market-value="{{ number_format($marketEstimate['value'], 2, '.', '') }}" data-market-change="{{ number_format($marketEstimate['change_percent'], 1, '.', '') }}" href="{{ route('week12.client.artwork', ['id' => $artwork->id]) }}"><em class="trending-nft-rank">#{{ sprintf('%02d', $loop->iteration + 1) }}</em><img src="{{ asset(str_starts_with($artwork->image, 'storage/') ? $artwork->image : 'assets/' . $artwork->image) }}" alt="{{ $artwork->title }}"><span><strong>{{ $artwork->title }}</strong><small>By {{ $artwork->creator }}</small></span><b>Ξ {{ number_format((float) $artwork->price, 2) }}</b></a>
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
            <article class="creator-mini-card">
              <img class="creator-mini-avatar" src="{{ asset($creator->avatar_path ?? 'assets/Pixmoree Neon Pixel Logo.png') }}" alt="{{ $creator->name }}">
              <div class="creator-mini-copy"><h3>{{ $creator->name }}</h3><p>{{ $creator->artworks_count }} {{ \Illuminate\Support\Str::plural('artwork', $creator->artworks_count) }}</p></div>
              <small class="creator-mini-sales"><i class="bi bi-gem"></i> {{ $homeContent['creators']['role_label'] }}</small>
            </article>
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
        @php($marketEstimate = $artwork->marketEstimate())
        <div class="col-6 col-sm-3 art-column" data-artwork-id="{{ $artwork->id }}" data-category="{{ \Illuminate\Support\Str::slug($artwork->category) }}" data-price="{{ $artwork->price }}" data-market-value="{{ number_format($marketEstimate['value'], 2, '.', '') }}" data-market-change="{{ number_format($marketEstimate['change_percent'], 1, '.', '') }}" data-created="{{ $artwork->created_at?->timestamp ?? 0 }}">
          <article class="art-card">
            <div class="art-image-wrap">
              <img src="{{ asset(str_starts_with($artwork->image, 'storage/') ? $artwork->image : 'assets/' . $artwork->image) }}" alt="{{ $artwork->title }} digital artwork">
              <span class="artwork-title">{{ $artwork->title }}</span>
              <button class="heart-button guest-heart" type="button" aria-label="Add {{ $artwork->title }} to favorites"><i class="bi bi-heart"></i></button>
            </div>
            <div class="art-card-body">
              <div class="art-creator">By <span>{{ $artwork->creator }}</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div>
              <div class="art-stats"><div class="art-price"><small>Asking price</small><strong>{{ number_format((float) $artwork->price, 2) }} ETH</strong><small class="art-market-estimate">Demo est. {{ number_format($marketEstimate['value'], 2) }} ETH <span>({{ $marketEstimate['change_percent'] >= 0 ? '+' : '' }}{{ number_format($marketEstimate['change_percent'], 1) }}%)</span></small></div><div class="art-items"><small>Artwork</small><strong>1</strong></div></div>
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
      <dl class="nft-preview-stats">
        <div id="nftPreviewRankRow" hidden><dt>Trending rank</dt><dd id="nftPreviewRank"></dd></div>
        <div id="nftPreviewPriceRow" hidden><dt>Floor price</dt><dd id="nftPreviewPrice"></dd></div>
        <div id="nftPreviewMarketValueRow" hidden><dt>Estimated value (demo)</dt><dd id="nftPreviewMarketValue"></dd></div>
        <div id="nftPreviewItemsRow" hidden><dt>Items</dt><dd id="nftPreviewItems"></dd></div>
      </dl>
      <a class="nft-preview-explore" href="#explore">Explore gallery <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        <a class="nft-preview-full-details" id="nftPreviewFullDetails" href="/#explore"><i class="bi bi-box-arrow-up-right"></i> View full details</a>
        <button class="nft-preview-collect" id="nftPreviewCollectButton" type="button" hidden><i class="bi bi-bag-plus"></i> Continue to payment</button>
      <p class="nft-preview-collect-feedback" id="nftPreviewCollectFeedback" role="status" hidden></p>
    </div>
  </div>
</dialog>
<script>window.pixmoreeAccount = <?= json_encode($account?->only(['name', 'email', 'phone', 'bio', 'pixel_coin_balance']) ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/site.js') }}?v=20261029-earnings"></script>
</body>
</html>
