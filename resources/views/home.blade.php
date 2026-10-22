<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pixmoree — Discover Digital Collectibles</title>
    <meta name="description" content="Explore digital art, anime-inspired collectibles, and creators on Pixmoree.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}?v=20261020-nft-details" rel="stylesheet">
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
        <a href="/user/notifications" class="member-notification-button member-only-nav-link" aria-label="Notifications" hidden><i class="bi bi-bell"></i><span id="memberTopNotificationBadge" hidden>0</span></a>
        <div class="member-profile" id="memberProfile" hidden>
          <button class="member-profile-toggle" id="memberProfileToggle" type="button" aria-expanded="false" aria-controls="memberProfileMenu"><span class="member-avatar" id="memberNavAvatar"><span class="member-avatar-initial">D</span><img id="memberNavAvatarImage" alt="" hidden></span><span id="memberNavName">Demo User</span><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
          <div class="member-profile-menu" id="memberProfileMenu" hidden>
            <span class="member-menu-heading">SIGNED IN AS A DEMO USER</span>
            <a href="/user" data-member-action="dashboard"><i class="bi bi-house"></i> Home</a>
            <a href="/user/collection" data-member-action="collection"><i class="bi bi-images"></i> My Collection <span id="memberMenuCollectionCount">0</span></a>
            <a href="/user/dashboard" data-member-action="dashboard"><i class="bi bi-grid"></i> Dashboard</a>
            <a href="/user/create" data-member-action="create"><i class="bi bi-plus-circle"></i> Create</a>
            <a href="/user/purchases" data-member-action="history"><i class="bi bi-receipt"></i> Purchases</a>
            <a href="/user/terms" data-member-action="terms"><i class="bi bi-shield-check"></i> Terms &amp; Originality</a>
            <a href="/user/profile-settings" data-member-action="profile-settings"><i class="bi bi-person-gear"></i> Profile Settings</a>
            <a href="/user/about" data-member-action="about"><i class="bi bi-info-circle"></i> About Pixmoree</a>
            <a href="#explore" class="member-menu-explore"><i class="bi bi-shop"></i> Explore NFTs</a>
            <a href="/" class="member-menu-exit" data-member-action="logout"><i class="bi bi-box-arrow-right"></i> Log out</a>
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
    <h1>Welcome!</h1><p class="hero-tagline">Enter a new dimension of digital creativity.</p>
    <ul class="hero-benefits">
      <li><i class="bi bi-check-circle"></i><span>Discover the extraordinary in digital art.</span></li>
      <li><i class="bi bi-check-circle"></i><span>Explore unique creations, collect digital treasures, and find art that speaks to you.</span></li>
    </ul>
    <a class="btn hero-explore" href="#explore">Explore</a>
  </div>
  <img class="hero-background-art" src="{{ asset('assets/guest-hero-background.png') }}" alt="" aria-hidden="true">
</section>
<main class="container-fluid px-3 px-lg-5 py-4">
    <section class="member-dashboard" id="memberWelcome" hidden aria-label="User account pages">
      <header class="member-dashboard-heading">
        <div><span class="member-welcome-kicker"><i class="bi bi-stars"></i> YOUR PIXMOREE ACCOUNT</span><h2>Welcome back, <span id="memberWelcomeName">Demo</span>!</h2><p>Discover art, keep track of your collection, and manage your account.</p></div>
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
            <div class="member-panel-heading"><div><h3>Dashboard</h3><p>Your account at a glance.</p></div><span class="member-demo-label">DEMO ACCOUNT</span></div>
            <article class="member-dashboard-profile-card">
              <img class="member-dashboard-banner" id="memberDashboardBanner" alt="" hidden>
              <span class="member-dashboard-avatar" id="memberDashboardAvatar"><span class="member-avatar-initial">D</span><img id="memberDashboardAvatarImage" alt="" hidden></span>
              <div class="member-dashboard-identity"><small>YOUR PROFILE</small><strong id="memberDashboardProfileName">Demo User</strong><span id="memberDashboardUsername">@demo-user</span></div>
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
          <section class="member-dashboard-panel" data-member-panel-content="payment" hidden>
            <div class="member-payment-heading"><div><span class="member-welcome-kicker"><i class="bi bi-shield-check"></i> PIXMOREE CHECKOUT</span><h3>Payment &amp; Transactions</h3><p>Review your order and choose a demo payment method.</p></div><span class="member-demo-label"><i class="bi bi-info-circle"></i> SIMULATION ONLY</span></div>
            <div class="member-payment-safety"><i class="bi bi-shield-lock"></i><span><strong>Safe demo checkout</strong> Payment providers are not connected. This page never asks for card/account numbers, passwords, PINs, or OTPs; no payment is sent or charged. Never share an OTP or wallet PIN in chat or with a seller.</span></div>
            <section class="member-panel-card member-pixel-wallet" aria-labelledby="memberPixelWalletHeading">
              <div class="member-wallet-overview">
                <span class="member-wallet-icon"><i class="bi bi-coin"></i></span>
                <div><small id="memberPixelWalletHeading">PIXMOREE DEMO WALLET</small><strong><span id="memberPixelCoinBalance">0</span> <span>PIXEL COIN</span></strong><p>Demo-only credits · no cash value</p></div>
              </div>
              <div class="member-wallet-topups">
                <strong>Add demo coins</strong>
                <div class="member-wallet-topup-options">
                  <button type="button" data-pixel-topup="500">+500 <small>PIXEL COIN</small></button>
                  <button type="button" data-pixel-topup="1000">+1,000 <small>PIXEL COIN</small></button>
                  <button type="button" data-pixel-topup="2500">+2,500 <small>PIXEL COIN</small></button>
                </div>
                <small>Top-ups instantly add sample credits in this browser. They are free demo credits—not deposits, purchases, or redeemable currency.</small>
                <p class="member-wallet-feedback" id="memberPixelWalletFeedback" role="status" aria-live="polite" hidden></p>
                <div class="member-wallet-activity" id="memberPixelCoinActivity" aria-live="polite"></div>
              </div>
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
              <h4>4. Purchases and demo transactions</h4><p>This version records sample purchase events in browser storage only. It does not process payments, create blockchain tokens, transfer ownership, or provide refunds. A production marketplace would need clear sale, refund, privacy, tax, and dispute terms before launch.</p>
              <h4>5. Respect intellectual property</h4><p>Creators retain rights they hold in their work. A listing or demo purchase does not transfer copyright unless a separate, valid agreement says so. Collectors should not reproduce, sell, or commercially exploit artwork without the creator’s permission or a license that allows it.</p>
              <h4>6. Policy updates and contact</h4><p>These draft rules may change as the project evolves. For a real service, publish the effective date, operator identity, contact and appeal process, governing law, and privacy terms after professional review.</p>
            </article>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="profile" hidden>
            <div class="member-panel-heading"><div><h3>Profile Settings</h3><p>Make your Pixmoree profile feel like yours.</p></div><span class="member-demo-label">YOUR PROFILE</span></div>
            <form class="member-profile-panel-form" id="memberProfileForm">
              <div class="member-profile-cover" id="memberProfileCover">
                <img id="memberProfileCoverImage" alt="" hidden>
                <div class="member-profile-cover-shade"><span><i class="bi bi-image"></i> Profile banner</span><label for="memberProfileBanner"><i class="bi bi-cloud-arrow-up"></i> Customize banner<input id="memberProfileBanner" name="banner" type="file" accept="image/png,image/jpeg,image/webp"></label></div>
              </div>
              <small class="member-profile-cover-help">Personalize your profile with a custom PNG, JPG, or WEBP banner (up to 1 MB).</small>
              <div class="member-profile-avatar" id="memberProfileAvatar"><span class="member-avatar-initial">D</span><img id="memberProfileAvatarImage" alt="Profile image preview" hidden></div>
              <div class="member-profile-intro"><strong id="memberProfilePreviewName">Demo User</strong><span id="memberProfilePreviewBio">Your creative profile on Pixmoree</span></div>
              <label class="member-avatar-upload" for="memberProfileImage">Change profile photo<input id="memberProfileImage" name="avatar" type="file" accept="image/png,image/jpeg,image/webp"><small>PNG, JPG, WEBP · up to 1 MB</small></label>
              <div class="member-profile-field"><label for="memberProfileName">Display name</label><input id="memberProfileName" name="name" type="text" value="Demo User" maxlength="40" required></div>
              <div class="member-profile-field"><label for="memberProfileEmail">Email address</label><input id="memberProfileEmail" name="email" type="email" value="demo@example.test" required></div>
              <div class="member-profile-field"><label for="memberProfilePhone">Phone</label><input id="memberProfilePhone" name="phone" type="tel" maxlength="30" placeholder="Add a phone number"></div>
              <div class="member-profile-field"><label for="memberProfileBio">About you</label><textarea id="memberProfileBio" name="bio" rows="3" maxlength="240" placeholder="Tell the community a little about yourself."></textarea></div>
              <div class="member-profile-field"><label for="memberProfileSocial">Social link</label><input id="memberProfileSocial" name="social" type="url" maxlength="200" placeholder="https://"></div>
              <label class="member-setting-option"><span><strong>Email notifications</strong><small>Receive updates about your demo account.</small></span><input id="memberEmailNotifications" type="checkbox" checked></label>
              <button class="member-profile-save" type="submit">Save profile</button>
              <p class="member-profile-feedback" id="memberProfileFeedback" role="status" hidden></p>
              <small>Demo only. Profile details stay in this browser and are not sent to a server.</small>
            </form>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="notifications" hidden>
            <div class="member-panel-heading"><div><h3>Notifications</h3><p>Recent activity from your collection and creator space.</p></div><button id="memberMarkNotificationsRead" class="member-notifications-read" type="button">Mark all as read</button></div>
            <div id="memberNotificationsList" class="member-notification-list"></div>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="about" hidden>
            <div class="member-about-page">
              <header class="about-hero">
                <div class="about-hero-copy">
                  <span class="about-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> OUR STORY</span>
                  <h1>A little more<br><span class="about-hero-brand-line">about <img class="about-hero-logo" src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree"></span></h1>
                  <p>A colorful corner of the internet for discovering digital art, meeting creators, and finding something that feels uniquely yours.</p>
                  <a class="about-hero-link" href="/user#explore">Explore the gallery <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                </div>
                <div class="about-hero-art">
                  <img src="{{ asset('assets/anime-style-character-space (2).jpg') }}" alt="Dreamlike character artwork among the stars">
                  <span><i class="bi bi-stars" aria-hidden="true"></i> Discover something new</span>
                </div>
              </header>
              <section class="about-content" aria-label="About Pixmoree">
                <article class="about-story-card">
                  <div class="about-story-image"><img src="{{ asset('assets/guest-hero-background.png') }}" alt="A colorful digital universe, representing the Pixmoree marketplace"><span class="about-image-mark"><i class="bi bi-sparkle" aria-hidden="true"></i></span></div>
                  <div class="about-story-copy"><span class="about-eyebrow">01 / ABOUT PIXMOREE</span><h2>Digital art, waiting to be discovered.</h2><p>Pixmoree is a digital collectibles marketplace concept focused on discovering and showcasing digital artwork. It brings imaginative pieces together in one place, making it easy to browse art and see the people behind it.</p></div>
                </article>
                <article class="about-story-card about-story-reverse">
                  <div class="about-story-image"><img src="{{ asset('assets/Neon Pixel Sparkle Sticker Sheet.png') }}" alt="A sheet of playful neon pixel-art characters and sparkles"><span class="about-image-mark"><i class="bi bi-heart" aria-hidden="true"></i></span></div>
                  <div class="about-story-copy"><span class="about-eyebrow">02 / OUR MISSION</span><h2>Make room for every kind of creator.</h2><p>Our goal is to offer a simple, visually engaging place where people can explore digital artwork and discover different artists. We want the experience to feel welcoming, inspiring, and fun to come back to.</p></div>
                </article>
                <article class="about-story-card">
                  <div class="about-story-image"><img src="{{ asset('assets/29108282_JEMA GER 1446-01.jpg') }}" alt="Anime-inspired digital artwork from the Pixmoree gallery"><span class="about-image-mark"><i class="bi bi-grid" aria-hidden="true"></i></span></div>
                  <div class="about-story-copy"><span class="about-eyebrow">03 / WHAT YOU CAN DO</span><h2>Explore at your own pace.</h2><p>Browse collections, open artwork to see its details, check out what is trending, and save pieces you love to your favorites. Pixmoree is designed to make finding your next inspiration feel effortless.</p><a class="about-inline-link" href="/user#explore">Browse the gallery <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
                </article>
              </section>
              <section class="about-project">
                <div class="about-project-image"><img src="{{ asset('assets/anime-character-using-virtual-reality-glasses-metaverse.jpg') }}" alt="A character exploring a virtual digital world"><div class="about-project-image-caption"><i class="bi bi-code-slash" aria-hidden="true"></i> Made while learning</div></div>
                <div class="about-project-copy">
                  <span class="about-eyebrow">04 / ABOUT THE PROJECT</span><h2>Built as a learning experience.</h2>
                  <p>Pixmoree is an educational project developed by students to practice web design, frontend development, and marketplace functionality. The site is a creative concept, made to bring those skills together in one interactive experience.</p>
                  <p class="about-project-started"><i class="bi bi-calendar3" aria-hidden="true"></i> Project started <time datetime="2026-10-15">October 15, 2026</time></p>
                  <div class="about-project-note"><i class="bi bi-lightbulb" aria-hidden="true"></i><span>Curiosity, creativity, and code — all in one project.</span></div>
                  <div class="about-tools"><span class="about-tools-label">Apps used</span><div class="about-tool-list">
                    <span class="about-tool"><span class="about-tool-logo about-tool-ae" aria-hidden="true">Ae</span><span>After Effects</span></span>
                    <span class="about-tool"><span class="about-tool-logo about-tool-ps" aria-hidden="true">Ps</span><span>Photoshop</span></span>
                    <span class="about-tool"><svg class="about-tool-vscode" viewBox="0 0 24 24" aria-hidden="true"><path fill="#007ACC" d="M17.2 2.1 7.3 11.2 2.1 7.1 0 8.5v7l2.1 1.4 5.2-4.1 9.9 9.1L24 19V5l-6.8-2.9Zm.1 4.1 3.2 1.4v8.8l-3.2 1.4-6.7-5.8 6.7-5.8Z"/><path fill="#1F9CF0" d="m2.1 7.1 5.2 4.1v1.6l-5.2 4.1L0 15.5v-7l2.1-1.4Z"/></svg><span>VS Code</span></span>
                  </div></div>
                </div>
              </section>
              <section class="about-student-brief" aria-label="Student project overview">
                <div class="about-student-brief-heading"><span class="about-eyebrow">STUDENT PROJECT OVERVIEW</span><h3>Purpose, scope, and learning goals.</h3></div>
                <div class="about-student-brief-grid">
                  <article><i class="bi bi-bullseye" aria-hidden="true"></i><h4>Project purpose</h4><p>Explore how a digital-art marketplace can help people discover artwork and creators through a clear, accessible interface.</p></article>
                  <article><i class="bi bi-stars" aria-hidden="true"></i><h4>Core features</h4><p>Artwork discovery, search and filters, favorites, creator listings, user collections, NFT detail views, and simulated checkout.</p></article>
                  <article><i class="bi bi-mortarboard" aria-hidden="true"></i><h4>Learning goals</h4><p>Practice responsive UI design, reusable page patterns, browser storage, form validation, accessibility, and responsible handling of creative work.</p></article>
                  <article><i class="bi bi-code-slash" aria-hidden="true"></i><h4>Tools and stack</h4><p>PHP/Laravel-compatible views, HTML, CSS, JavaScript, and Bootstrap, with Photoshop and After Effects for visual assets.</p></article>
                  <article><i class="bi bi-journal-check" aria-hidden="true"></i><h4>Scope and limitations</h4><p>This build is a student prototype. Accounts, listings, transactions, Pixel Coin, and payments are browser-based demonstrations—not production services.</p></article>
                </div>
              </section>
              <section class="about-team" aria-labelledby="member-about-team-title">
                <div class="about-team-heading"><span class="about-eyebrow">05 / MEET THE CREATORS</span><h2 id="member-about-team-title">The people behind Pixmoree.</h2><p>We’re a student team bringing design, code, and creativity together. Replace these profile placeholders with your team’s details.</p></div>
                <div class="about-team-grid">
                  <article class="about-team-card"><div class="about-team-image"><img src="{{ asset('assets/anime-style-character-space.jpg') }}" alt="Artwork placeholder for team member one"><span>01</span></div><div class="about-team-details"><span class="about-team-role">Project Lead · UI/UX Designer</span><h3>[Your Name]</h3><p class="about-team-course">[Course / Program] <span>·</span> [Year Level]</p><blockquote>“[Add a short quote about your role or what inspired you.]”</blockquote><div class="about-team-contact"><span><i class="bi bi-instagram" aria-hidden="true"></i> @your-handle</span><span><i class="bi bi-envelope" aria-hidden="true"></i> your.email@example.com</span><span><i class="bi bi-telephone" aria-hidden="true"></i> +63 XXX XXX XXXX</span></div></div></article>
                  <article class="about-team-card"><div class="about-team-image"><img src="{{ asset('assets/29108282_JEMA GER 1446-01.jpg') }}" alt="Artwork placeholder for team member two"><span>02</span></div><div class="about-team-details"><span class="about-team-role">Frontend Developer</span><h3>[Your Name]</h3><p class="about-team-course">[Course / Program] <span>·</span> [Year Level]</p><blockquote>“[Add a short quote about your role or what inspired you.]”</blockquote><div class="about-team-contact"><span><i class="bi bi-github" aria-hidden="true"></i> @your-handle</span><span><i class="bi bi-envelope" aria-hidden="true"></i> your.email@example.com</span><span><i class="bi bi-telephone" aria-hidden="true"></i> +63 XXX XXX XXXX</span></div></div></article>
                  <article class="about-team-card"><div class="about-team-image"><img src="{{ asset('assets/anime-style-character-space (1).jpg') }}" alt="Artwork placeholder for team member three"><span>03</span></div><div class="about-team-details"><span class="about-team-role">Marketplace Developer</span><h3>[Your Name]</h3><p class="about-team-course">[Course / Program] <span>·</span> [Year Level]</p><blockquote>“[Add a short quote about your role or what inspired you.]”</blockquote><div class="about-team-contact"><span><i class="bi bi-instagram" aria-hidden="true"></i> @your-handle</span><span><i class="bi bi-envelope" aria-hidden="true"></i> your.email@example.com</span><span><i class="bi bi-telephone" aria-hidden="true"></i> +63 XXX XXX XXXX</span></div></div></article>
                  <article class="about-team-card"><div class="about-team-image"><img src="{{ asset('assets/anime-character-using-virtual-reality-glasses-metaverse.jpg') }}" alt="Artwork placeholder for team member four"><span>04</span></div><div class="about-team-details"><span class="about-team-role">Content &amp; Visual Designer</span><h3>[Your Name]</h3><p class="about-team-course">[Course / Program] <span>·</span> [Year Level]</p><blockquote>“[Add a short quote about your role or what inspired you.]”</blockquote><div class="about-team-contact"><span><i class="bi bi-instagram" aria-hidden="true"></i> @your-handle</span><span><i class="bi bi-envelope" aria-hidden="true"></i> your.email@example.com</span><span><i class="bi bi-telephone" aria-hidden="true"></i> +63 XXX XXX XXXX</span></div></div></article>
                </div>
                <p class="about-team-privacy"><i class="bi bi-shield-check" aria-hidden="true"></i> Only publish contact details and social profiles that each team member has agreed to share.</p>
              </section>
              <section class="about-contact">
                <div class="about-contact-icon"><i class="bi bi-chat-square-heart" aria-hidden="true"></i></div>
                <div><span class="about-eyebrow">06 / CONTACT &amp; PROJECT INFO</span><h2>Have a question about the project?</h2><p>For project questions, use the team contact details above once your members have chosen what they want to share, or reach out through your course or class channel.</p></div>
                <a href="/user" class="about-contact-link">Back to home <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
              </section>
            </div>
          </section>
          <section class="member-dashboard-panel" data-member-panel-content="create" hidden>
            <div class="member-panel-heading"><div><h3>Create a listing</h3><p>Share original artwork with the Pixmoree community.</p></div><span class="member-demo-label">DEMO SELLER</span></div>
            <form class="member-create-form" id="memberCreateForm">
              <label class="member-create-upload" for="memberArtworkFile"><i class="bi bi-cloud-arrow-up"></i><strong>Choose an artwork image</strong><span>PNG, JPG, or WEBP · up to 2 MB</span><img id="memberArtworkPreview" alt="Artwork preview" hidden></label>
              <input id="memberArtworkFile" name="image" type="file" accept="image/png,image/jpeg,image/webp">
              <label for="memberArtworkTitle">Artwork title<input id="memberArtworkTitle" name="title" type="text" maxlength="60" placeholder="e.g. Starlight Wanderer" required></label>
              <label for="memberArtworkDescription">Description<textarea id="memberArtworkDescription" name="description" rows="4" maxlength="400" placeholder="Tell collectors about your artwork." required></textarea></label>
              <div class="member-create-form-row"><label for="memberArtworkPrice">Price (ETH)<input id="memberArtworkPrice" name="price" type="number" min="0.01" max="100000" step="0.01" placeholder="0.25" required></label><label for="memberArtworkCategory">Category<select id="memberArtworkCategory" name="category"><option>Digital Art</option><option>Illustration</option><option>Photography</option><option>3D Art</option></select></label></div>
              <label class="member-policy-accept"><input id="memberArtworkOriginality" name="originality" type="checkbox" required> I created this work or have permission to list it, and it does not knowingly violate anyone else’s rights. I agree to the <a href="/user/terms">Terms &amp; Originality Policy</a>.</label>
              <button class="member-profile-save" type="submit"><i class="bi bi-plus-lg"></i> Create listing</button>
              <p class="member-profile-feedback" id="memberCreateFeedback" role="status" hidden></p>
              <small>Demo only: listings are saved in this browser. No payment or real marketplace listing is created.</small>
            </form>
          </section>
        </div>
      </div>
    </section>
    <section id="trending" class="trending-nft-section">
      <div class="trending-nft-heading"><div><h2>Trending</h2><p>Trending NFT's today</p></div><a href="#explore" class="trending-view-all">View all <i class="bi bi-arrow-up-right"></i></a></div>
      <div class="trending-nft-grid">
        <article class="trending-feature" aria-label="Featured trending NFTs">
          <div class="trending-feature-slides" aria-live="polite">
            <div class="trending-feature-slide active" data-title="Anime Girl" data-creator="Insan.Rin" data-floor="Ξ 4,403.94K" data-items="43" aria-hidden="false">
              <img src="{{ asset('assets/29108282_JEMA GER 1446-01.jpg') }}" alt="Anime Girl digital collectible">
            </div>
            <div class="trending-feature-slide" data-title="Anime Boy" data-creator="Insan.Rin" data-floor="Ξ 1,334.0K" data-items="38" aria-hidden="true">
              <img src="{{ asset('assets/anime-style-character-space.jpg') }}" alt="Anime Boy digital collectible">
            </div>
            <div class="trending-feature-slide" data-title="Space" data-creator="Insan.Rin" data-floor="Ξ 1,520.0K" data-items="52" aria-hidden="true">
              <img src="{{ asset('assets/anime-style-character-space (2).jpg') }}" alt="Space digital collectible">
            </div>
            <div class="trending-feature-slide" data-title="Chinese Zombie" data-creator="Insan.Rin" data-floor="Ξ 1,543.0K" data-items="29" aria-hidden="true">
              <img src="{{ asset('assets/cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png') }}" alt="Chinese Zombie digital collectible">
            </div>
            <div class="trending-feature-slide" data-title="Astro Boy" data-creator="Insan.Rin" data-floor="Ξ 1,132.1K" data-items="47" aria-hidden="true">
              <img src="{{ asset('assets/anime-character-using-virtual-reality-glasses-metaverse.jpg') }}" alt="Astro Boy digital collectible">
            </div>
          </div>
          <div class="trending-feature-info">
            <div><h3 class="trending-feature-title">Anime Girl <i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p class="trending-feature-creator">By Insan.Rin</p></div>
            <div class="trending-feature-stats"><div><small>Floor Price</small><strong class="trending-feature-floor">Ξ 4,403.94K</strong></div><div><small>Items</small><strong class="trending-feature-items">43</strong></div></div>
          </div>
          <button class="trending-feature-arrow trending-feature-prev" type="button" aria-label="Previous featured NFT"><i class="bi bi-chevron-left"></i></button>
          <button class="trending-feature-arrow trending-feature-next" type="button" aria-label="Next featured NFT"><i class="bi bi-chevron-right"></i></button>
          <div class="trending-nft-pagination" role="group" aria-label="Choose featured NFT">
            <button class="active" type="button" aria-label="Show featured NFT 1" aria-current="true"></button>
            <button type="button" aria-label="Show featured NFT 2" aria-current="false"></button>
            <button type="button" aria-label="Show featured NFT 3" aria-current="false"></button>
            <button type="button" aria-label="Show featured NFT 4" aria-current="false"></button>
            <button type="button" aria-label="Show featured NFT 5" aria-current="false"></button>
          </div>
        </article>
        <div class="trending-nft-list" aria-label="Trending NFT ranks 6 through 15">
          <a class="trending-nft-item" href="#explore"><em class="trending-nft-rank">#06</em><img src="{{ asset('assets/anime-style-character-space.jpg') }}" alt=""><span><strong>Neon Wanderer</strong><small>By pixel.dreams</small></span><b>Ξ 1,334.0K</b></a>
          <a class="trending-nft-item" href="#explore"><em class="trending-nft-rank">#07</em><img src="{{ asset('assets/anime-style-character-space (2).jpg') }}" alt=""><span><strong>Starlight Drift</strong><small>By cosmic.art</small></span><b>Ξ 1,520.0K</b></a>
          <a class="trending-nft-item" href="#explore"><em class="trending-nft-rank">#08</em><img src="{{ asset('assets/cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png') }}" alt=""><span><strong>Moonlit Guardian</strong><small>By neko.studio</small></span><b>Ξ 1,543.0K</b></a>
          <a class="trending-nft-item" href="#explore"><em class="trending-nft-rank">#09</em><img src="{{ asset('assets/anime-character-using-virtual-reality-glasses-metaverse.jpg') }}" alt=""><span><strong>Virtual Voyager</strong><small>By astro.design</small></span><b>Ξ 1,132.1K</b></a>
          <a class="trending-nft-item" href="#explore"><em class="trending-nft-rank">#10</em><img src="{{ asset('assets/29108282_JEMA GER 1446-01.jpg') }}" alt=""><span><strong>Dreamscape Bloom</strong><small>By jema.works</small></span><b>Ξ 1,098.5K</b></a>
          <a class="trending-nft-item" href="#explore"><em class="trending-nft-rank">#11</em><img src="{{ asset('assets/36101435_7200_4_03.jpg') }}" alt=""><span><strong>Pixel Ronin</strong><small>By pixel.soul</small></span><b>Ξ 987.2K</b></a>
          <a class="trending-nft-item" href="#explore"><em class="trending-nft-rank">#12</em><img src="{{ asset('assets/anime-style-character-space (3).jpg') }}" alt=""><span><strong>Dragon Reverie</strong><small>By dreamforge</small></span><b>Ξ 945.8K</b></a>
          <a class="trending-nft-item" href="#explore"><em class="trending-nft-rank">#13</em><img src="{{ asset('assets/anime-style-character-space (1).jpg') }}" alt=""><span><strong>Cosmic Daydream</strong><small>By neon.muse</small></span><b>Ξ 902.4K</b></a>
          <a class="trending-nft-item" href="#explore"><em class="trending-nft-rank">#14</em><img src="{{ asset('assets/cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png') }}" alt=""><span><strong>Foxfire Samurai</strong><small>By kitsu.art</small></span><b>Ξ 876.0K</b></a>
          <a class="trending-nft-item" href="#explore"><em class="trending-nft-rank">#15</em><img src="{{ asset('assets/Neon Pixel Sparkle Sticker Sheet.png') }}" alt=""><span><strong>Orbit of Stars</strong><small>By orbit.draws</small></span><b>Ξ 841.6K</b></a>
        </div>
      </div>
    </section>
    <section class="creator-strip-section">
    <div class="creator-strip-heading"><h2>Trending Creators</h2><p>Artists making waves this week</p></div>
    <div class="creator-carousel" aria-label="Trending creators">
      <button class="creator-arrow creator-arrow-prev" type="button" aria-label="Scroll creators left"><i class="bi bi-chevron-left"></i></button>
      <div class="creator-track" id="creatorTrack">
        <article class="creator-mini-card">
          <img class="creator-mini-avatar" src="{{ asset('assets/anime-style-character-space.jpg') }}" alt="">
          <div class="creator-mini-copy"><h3>pixel.dreams<i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p>12.8K <span>↗ +12.4%</span></p></div>
          <small class="creator-mini-sales"><i class="bi bi-gem"></i> 04.0k</small>
        </article>
        <article class="creator-mini-card">
          <img class="creator-mini-avatar" src="{{ asset('assets/anime-style-character-space (1).jpg') }}" alt="">
          <div class="creator-mini-copy"><h3>cosmic.art<i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p>9.4K <span>↗ +12.4%</span></p></div>
          <small class="creator-mini-sales"><i class="bi bi-gem"></i> 04.0k</small>
        </article>
        <article class="creator-mini-card">
          <img class="creator-mini-avatar" src="{{ asset('assets/cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png') }}" alt="">
          <div class="creator-mini-copy"><h3>neko.studio<i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p>8.2K <span>↗ +12.4%</span></p></div>
          <small class="creator-mini-sales"><i class="bi bi-gem"></i> 04.0k</small>
        </article>
        <article class="creator-mini-card">
          <img class="creator-mini-avatar" src="{{ asset('assets/anime-character-using-virtual-reality-glasses-metaverse.jpg') }}" alt="">
          <div class="creator-mini-copy"><h3>astro.design<i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p>6.7K <span>↗ +12.4%</span></p></div>
          <small class="creator-mini-sales"><i class="bi bi-gem"></i> 04.0k</small>
        </article>
        <article class="creator-mini-card">
          <img class="creator-mini-avatar" src="{{ asset('assets/29108282_JEMA GER 1446-01.jpg') }}" alt="">
          <div class="creator-mini-copy"><h3>jema.works<i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p>5.9K <span>↗ +10.8%</span></p></div>
          <small class="creator-mini-sales"><i class="bi bi-gem"></i> 03.8k</small>
        </article>
        <article class="creator-mini-card">
          <img class="creator-mini-avatar" src="{{ asset('assets/36101435_7200_4_03.jpg') }}" alt="">
          <div class="creator-mini-copy"><h3>pixel.soul<i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p>5.2K <span>↗ +9.6%</span></p></div>
          <small class="creator-mini-sales"><i class="bi bi-gem"></i> 03.5k</small>
        </article>
        <article class="creator-mini-card">
          <img class="creator-mini-avatar" src="{{ asset('assets/anime-style-character-space (3).jpg') }}" alt="">
          <div class="creator-mini-copy"><h3>dreamforge<i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p>4.8K <span>↗ +8.9%</span></p></div>
          <small class="creator-mini-sales"><i class="bi bi-gem"></i> 03.1k</small>
        </article>
        <article class="creator-mini-card">
          <img class="creator-mini-avatar" src="{{ asset('assets/anime-style-character-space (2).jpg') }}" alt="">
          <div class="creator-mini-copy"><h3>neon.muse<i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p>4.3K <span>↗ +8.1%</span></p></div>
          <small class="creator-mini-sales"><i class="bi bi-gem"></i> 02.9k</small>
        </article>
        <article class="creator-mini-card">
          <img class="creator-mini-avatar" src="{{ asset('assets/anime-style-character-space (1).jpg') }}" alt="">
          <div class="creator-mini-copy"><h3>moonbyte<i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p>3.9K <span>↗ +7.4%</span></p></div>
          <small class="creator-mini-sales"><i class="bi bi-gem"></i> 02.6k</small>
        </article>
        <article class="creator-mini-card">
          <img class="creator-mini-avatar" src="{{ asset('assets/cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png') }}" alt="">
          <div class="creator-mini-copy"><h3>kitsu.art<i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p>3.4K <span>↗ +6.7%</span></p></div>
          <small class="creator-mini-sales"><i class="bi bi-gem"></i> 02.2k</small>
        </article>
        <article class="creator-mini-card">
          <img class="creator-mini-avatar" src="{{ asset('assets/anime-character-using-virtual-reality-glasses-metaverse.jpg') }}" alt="">
          <div class="creator-mini-copy"><h3>orbit.draws<i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p>3.1K <span>↗ +5.9%</span></p></div>
          <small class="creator-mini-sales"><i class="bi bi-gem"></i> 01.9k</small>
        </article>
        <article class="creator-mini-card">
          <img class="creator-mini-avatar" src="{{ asset('assets/Neon Pixel Sparkle Sticker Sheet.png') }}" alt="">
          <div class="creator-mini-copy"><h3>starlit.art<i class="bi bi-patch-check-fill" aria-label="Verified"></i></h3><p>2.8K <span>↗ +5.2%</span></p></div>
          <small class="creator-mini-sales"><i class="bi bi-gem"></i> 01.7k</small>
        </article>
      </div>
      <button class="creator-arrow creator-arrow-next" type="button" aria-label="Scroll creators right"><i class="bi bi-chevron-right"></i></button>
    </div>
    <div class="creator-pagination" aria-hidden="true"><span class="active"></span><span></span><span></span></div>
    <span id="creators" class="creator-anchor" aria-hidden="true"></span>
  </section>
  <section id="explore" class="explore-section">
    <div class="section-heading explore-heading"><div><h2>Explore <i class="bi bi-compass"></i></h2><p class="muted">Find your next favorite piece</p></div></div>
    <div class="filter-row"><div class="filter-pills"><button class="filter-pill active" data-filter="all">All items</button><button class="filter-pill" data-filter="newest">Newest</button><button class="filter-pill" data-filter="price">Price</button><button class="filter-pill" data-filter="favorites">Favorites</button></div><label class="sort-button"><i class="bi bi-sort-down"></i><span>Sort by</span><select id="sortSelect" aria-label="Sort artworks"><option value="default">Default</option><option value="price-low">Price: low to high</option><option value="price-high">Price: high to low</option></select></label></div>
    <div class="row g-3" id="artGrid">
      <div class="col-6 col-sm-3 art-column" data-category="anime" data-price="2.40" data-created="8"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/anime-style-character-space.jpg') }}" alt="Anime boy digital collectible"><span class="artwork-title">ANIME BOY #032</span><button class="heart-button guest-heart" type="button" aria-label="Add Anime Boy to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>pixel.dreams</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>2.40 ETH</strong></div><div class="art-items"><small>Items</small><strong>18</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="space" data-price="1.85" data-created="7"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/anime-style-character-space (1).jpg') }}" alt="Samurai in a dreamlike landscape"><span class="artwork-title">SAMURAI #8423</span><button class="heart-button guest-heart" type="button" aria-label="Add Samurai to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>cosmic.art</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>1.85 ETH</strong></div><div class="art-items"><small>Items</small><strong>19</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="space" data-price="3.10" data-created="6"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/anime-style-character-space (2).jpg') }}" alt="Colorful space-inspired artwork"><span class="artwork-title">SPACE BOY #043</span><button class="heart-button guest-heart" type="button" aria-label="Add Space Boy to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>neko.studio</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>3.10 ETH</strong></div><div class="art-items"><small>Items</small><strong>18</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="anime" data-price="2.75" data-created="5"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/anime-style-character-space (3).jpg') }}" alt="Fantasy dragon girl artwork"><span class="artwork-title">DRAGON GIRL #02</span><button class="heart-button guest-heart" type="button" aria-label="Add Dragon Girl to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>pixel.dreams</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>2.75 ETH</strong></div><div class="art-items"><small>Items</small><strong>19</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="character" data-price="1.60" data-created="4"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/anime-character-using-virtual-reality-glasses-metaverse.jpg') }}" alt="Anime character portrait"><span class="artwork-title">GOJO SATORU #083</span><button class="heart-button guest-heart" type="button" aria-label="Add Gojo Satoru to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>cosmic.art</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>1.60 ETH</strong></div><div class="art-items"><small>Items</small><strong>18</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="character" data-price="0.95" data-created="3"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/36101435_7200_4_03.jpg') }}" alt="Anime samurai collectible"><span class="artwork-title">SOLO LEVELING #8423</span><button class="heart-button guest-heart" type="button" aria-label="Add Solo Leveling to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>neko.studio</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>0.95 ETH</strong></div><div class="art-items"><small>Items</small><strong>19</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="anime" data-price="1.25" data-created="2"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png') }}" alt="Cute astronaut samurai with fox mask"><span class="artwork-title">DASTLE #042</span><button class="heart-button guest-heart" type="button" aria-label="Add Dastle to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>pixel.dreams</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>1.25 ETH</strong></div><div class="art-items"><small>Items</small><strong>18</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="anime" data-price="2.20" data-created="1"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/29108282_JEMA GER 1446-01.jpg') }}" alt="Anime portrait artwork"><span class="artwork-title">ITACHI #02</span><button class="heart-button guest-heart" type="button" aria-label="Add Itachi to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>cosmic.art</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>2.20 ETH</strong></div><div class="art-items"><small>Items</small><strong>12</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="space" data-price="1.45" data-created="9"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/anime-style-character-space (2).jpg') }}" alt="Neon orbit digital artwork"><span class="artwork-title">NEON ORBIT #018</span><button class="heart-button guest-heart" type="button" aria-label="Add Neon Orbit to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>astro.design</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>1.45 ETH</strong></div><div class="art-items"><small>Items</small><strong>24</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="character" data-price="2.05" data-created="10"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/anime-character-using-virtual-reality-glasses-metaverse.jpg') }}" alt="Dream traveler digital collectible"><span class="artwork-title">DREAM TRAVELER #12</span><button class="heart-button guest-heart" type="button" aria-label="Add Dream Traveler to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>moonbyte</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>2.05 ETH</strong></div><div class="art-items"><small>Items</small><strong>16</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="anime" data-price="1.75" data-created="11"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/anime-style-character-space (3).jpg') }}" alt="Crimson dragon digital collectible"><span class="artwork-title">CRIMSON DRAGON #07</span><button class="heart-button guest-heart" type="button" aria-label="Add Crimson Dragon to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>dreamforge</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>1.75 ETH</strong></div><div class="art-items"><small>Items</small><strong>21</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="space" data-price="0.88" data-created="12"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/Neon Pixel Sparkle Sticker Sheet.png') }}" alt="Stardust sparkle digital artwork"><span class="artwork-title">STARDUST #301</span><button class="heart-button guest-heart" type="button" aria-label="Add Stardust to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>starlit.art</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>0.88 ETH</strong></div><div class="art-items"><small>Items</small><strong>31</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="anime" data-price="2.60" data-created="13"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/29108282_JEMA GER 1446-01.jpg') }}" alt="Moonlit portrait digital artwork"><span class="artwork-title">MOONLIT #044</span><button class="heart-button guest-heart" type="button" aria-label="Add Moonlit to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>jema.works</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>2.60 ETH</strong></div><div class="art-items"><small>Items</small><strong>14</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="character" data-price="1.18" data-created="14"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/36101435_7200_4_03.jpg') }}" alt="Shadow ronin digital collectible"><span class="artwork-title">SHADOW RONIN #09</span><button class="heart-button guest-heart" type="button" aria-label="Add Shadow Ronin to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>pixel.soul</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>1.18 ETH</strong></div><div class="art-items"><small>Items</small><strong>27</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="space" data-price="1.92" data-created="15"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/anime-style-character-space (1).jpg') }}" alt="Cosmic samurai digital artwork"><span class="artwork-title">COSMIC SAMURAI #5</span><button class="heart-button guest-heart" type="button" aria-label="Add Cosmic Samurai to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>kitsu.art</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>1.92 ETH</strong></div><div class="art-items"><small>Items</small><strong>20</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="character" data-price="1.35" data-created="16"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png') }}" alt="Foxfire astronaut digital collectible"><span class="artwork-title">FOXFIRE #016</span><button class="heart-button guest-heart" type="button" aria-label="Add Foxfire to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>neko.studio</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>1.35 ETH</strong></div><div class="art-items"><small>Items</small><strong>22</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="anime" data-price="0.76" data-created="17"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/anime-style-character-space.jpg') }}" alt="Pixel dream digital artwork"><span class="artwork-title">PIXEL DREAM #128</span><button class="heart-button guest-heart" type="button" aria-label="Add Pixel Dream to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>pixel.dreams</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>0.76 ETH</strong></div><div class="art-items"><small>Items</small><strong>35</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="space" data-price="1.08" data-created="18"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/anime-style-character-space (2).jpg') }}" alt="Orbit glow digital artwork"><span class="artwork-title">ORBIT GLOW #020</span><button class="heart-button guest-heart" type="button" aria-label="Add Orbit Glow to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>orbit.draws</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>1.08 ETH</strong></div><div class="art-items"><small>Items</small><strong>28</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="anime" data-price="1.64" data-created="19"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/29108282_JEMA GER 1446-01.jpg') }}" alt="Aurora portrait digital artwork"><span class="artwork-title">AURORA VEIL #019</span><button class="heart-button guest-heart" type="button" aria-label="Add Aurora Veil to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>jema.works</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>1.64 ETH</strong></div><div class="art-items"><small>Items</small><strong>17</strong></div></div></div></article></div>
      <div class="col-6 col-sm-3 art-column" data-category="character" data-price="0.92" data-created="20"><article class="art-card"><div class="art-image-wrap"><img src="{{ asset('assets/cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png') }}" alt="Kitsune star digital collectible"><span class="artwork-title">KITSUNE STAR #020</span><button class="heart-button guest-heart" type="button" aria-label="Add Kitsune Star to favorites"><i class="bi bi-heart"></i></button></div><div class="art-card-body"><div class="art-creator">By <span>kitsu.art</span><i class="bi bi-patch-check-fill" aria-label="Verified"></i></div><div class="art-stats"><div class="art-price"><small>Floor Price</small><strong>0.92 ETH</strong></div><div class="art-items"><small>Items</small><strong>26</strong></div></div></div></article></div>
    </div>
    <div class="load-more-wrap"><button class="btn-load" id="loadMoreItems" type="button"><i class="bi bi-plus-circle" aria-hidden="true"></i> Load more items</button></div>
  </section>
  <section id="about" class="guest-about"><h2>Creativity has a new home.</h2><p>Explore digital art, discover emerging creators, and find your next favorite piece on Pixmoree.</p><a href="/login" class="btn hero-explore">Join the community</a></section>
  <footer class="site-footer"><a href="#"><img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree" class="footer-logo"></a><p>Discover a universe of digital creativity.</p><div class="footer-links"><a href="#explore">Explore</a><a href="#creators">Creators</a><a href="#">Community</a><a href="#">Support</a></div><small>© {{ date('Y') }} Pixmoree. Made for creators and collectors.</small></footer>
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
        <div id="nftPreviewItemsRow" hidden><dt>Items</dt><dd id="nftPreviewItems"></dd></div>
      </dl>
      <a class="nft-preview-explore" href="#explore">Explore gallery <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        <a class="nft-preview-full-details" id="nftPreviewFullDetails" href="/#explore"><i class="bi bi-box-arrow-up-right"></i> View full details</a>
        <button class="nft-preview-collect" id="nftPreviewCollectButton" type="button" hidden><i class="bi bi-bag-plus"></i> Continue to payment</button>
      <p class="nft-preview-collect-feedback" id="nftPreviewCollectFeedback" role="status" hidden></p>
    </div>
  </div>
</dialog>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/site.js') }}?v=20261020-nft-details"></script>
</body>
</html>
