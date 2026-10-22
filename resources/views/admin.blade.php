<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin dashboard — Pixmoree</title>
    <meta name="description" content="Pixmoree student-project administration dashboard demo.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
</head>
<body class="admin-page">
<div class="admin-shell">
  <aside class="admin-sidebar" id="adminSidebar">
    <a class="admin-brand" href="/" aria-label="Pixmoree home"><img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree"><span>ADMIN</span></a>
    <div class="admin-workspace"><span class="admin-workspace-icon"><i class="bi bi-stars" aria-hidden="true"></i></span><span><strong>Pixmoree</strong><small>Marketplace demo</small></span><i class="bi bi-chevron-expand" aria-hidden="true"></i></div>
    <p class="admin-nav-label">WORKSPACE</p>
    <nav class="admin-nav" aria-label="Admin navigation">
      <button class="active" type="button" data-admin-nav="overview"><i class="bi bi-grid-1x2" aria-hidden="true"></i> Dashboard</button>
      <button type="button" data-admin-nav="nfts"><i class="bi bi-image" aria-hidden="true"></i> NFT Management <span class="admin-nav-count" id="nftNavCount">128</span></button>
      <button type="button" data-admin-nav="users"><i class="bi bi-people" aria-hidden="true"></i> User Management</button>
      <button type="button" data-admin-nav="transactions"><i class="bi bi-receipt" aria-hidden="true"></i> Transactions</button>
      <button type="button" data-admin-nav="categories"><i class="bi bi-tags" aria-hidden="true"></i> Categories</button>
    </nav>
    <p class="admin-nav-label admin-nav-label-lower">PREFERENCES</p>
    <nav class="admin-nav" aria-label="Admin preferences">
      <button type="button" data-admin-nav="settings"><i class="bi bi-gear" aria-hidden="true"></i> Settings</button>
    </nav>
    <div class="admin-sidebar-bottom">
      <div class="admin-sidebar-help"><span><i class="bi bi-info-circle" aria-hidden="true"></i></span><div><strong>Demo workspace</strong><small>Changes are temporary</small></div></div>
      <a class="admin-logout" href="/"><i class="bi bi-box-arrow-left" aria-hidden="true"></i> Exit admin demo</a>
      <a class="admin-user-mini" href="/login"><span class="admin-avatar">A</span><span><strong>Demo Admin</strong><small>Administrator</small></span><i class="bi bi-three-dots" aria-hidden="true"></i></a>
    </div>
  </aside>

  <main class="admin-main">
    <header class="admin-topbar">
      <button class="admin-mobile-menu" type="button" id="adminMenuToggle" aria-label="Open admin navigation" aria-controls="adminSidebar" aria-expanded="false"><i class="bi bi-list" aria-hidden="true"></i></button>
      <div class="admin-breadcrumb"><span>Pixmoree</span><i class="bi bi-chevron-right" aria-hidden="true"></i><strong id="adminBreadcrumb">Dashboard</strong></div>
      <div class="admin-topbar-actions">
        <label class="admin-search"><i class="bi bi-search" aria-hidden="true"></i><input id="adminSearch" type="search" placeholder="Search this page..." aria-label="Search current admin page"><kbd>⌘ K</kbd></label>
        <button class="admin-icon-button" type="button" aria-label="Notifications"><i class="bi bi-bell" aria-hidden="true"></i><span></span></button>
        <span class="admin-avatar">A</span>
      </div>
    </header>

    <div class="admin-demo-banner"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span><strong>Demo admin UI:</strong> all numbers and accounts are fictional. Actions only change this browser session; no backend, real user data, or transactions are connected.</span></div>

    <section class="admin-panel active" data-admin-panel="overview">
      <div class="admin-page-heading">
        <div><p class="admin-overline">PIXMOREE / ADMIN</p><h1>Dashboard overview</h1><p>Here’s what’s happening in your marketplace demo.</p></div>
        <button class="admin-date-button" type="button"><i class="bi bi-calendar3" aria-hidden="true"></i> Last 30 days <i class="bi bi-chevron-down" aria-hidden="true"></i></button>
      </div>
      <div class="admin-stat-grid">
        <article class="admin-stat-card"><div class="admin-stat-top"><span>Total NFTs</span><i class="bi bi-image"></i></div><strong id="statNfts">128</strong><small><span class="admin-trend-up"><i class="bi bi-arrow-up-right"></i> 12%</span> vs. last month</small><div class="admin-stat-sparkline spark-purple"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div></article>
        <article class="admin-stat-card"><div class="admin-stat-top"><span>Registered users</span><i class="bi bi-people"></i></div><strong>56</strong><small><span class="admin-trend-up"><i class="bi bi-arrow-up-right"></i> 8%</span> vs. last month</small><div class="admin-stat-sparkline spark-blue"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div></article>
        <article class="admin-stat-card"><div class="admin-stat-top"><span>Listed artworks</span><i class="bi bi-box-seam"></i></div><strong id="statListed">84</strong><small><span class="admin-trend-up"><i class="bi bi-arrow-up-right"></i> 6%</span> vs. last month</small><div class="admin-stat-sparkline spark-pink"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div></article>
        <article class="admin-stat-card"><div class="admin-stat-top"><span>Simulated sales</span><i class="bi bi-currency-exchange"></i></div><strong>32</strong><small><span class="admin-trend-up"><i class="bi bi-arrow-up-right"></i> 18%</span> vs. last month</small><div class="admin-stat-sparkline spark-green"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div></article>
      </div>
      <div class="admin-overview-grid">
        <section class="admin-surface admin-chart-card">
          <div class="admin-surface-heading"><div><h2>Marketplace activity</h2><p>Demo sales and listings · last 7 days</p></div><button class="admin-more-button" type="button" aria-label="More chart options"><i class="bi bi-three-dots"></i></button></div>
          <div class="admin-chart-legend"><span><i class="legend-sales"></i> Sales</span><span><i class="legend-listings"></i> New listings</span></div>
          <div class="admin-chart" role="img" aria-label="Illustrative chart showing fictional sales and listings during the last week">
            <div class="admin-chart-y"><span>40</span><span>30</span><span>20</span><span>10</span><span>0</span></div>
            <div class="admin-chart-plot"><div class="admin-chart-gridlines"><i></i><i></i><i></i><i></i><i></i></div><svg viewBox="0 0 700 190" preserveAspectRatio="none" aria-hidden="true"><defs><linearGradient id="salesFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#bb55eb" stop-opacity=".28"/><stop offset="1" stop-color="#bb55eb" stop-opacity="0"/></linearGradient></defs><path d="M0 142 C42 138 53 119 100 125 S157 147 200 108 S258 113 300 95 S354 111 400 73 S453 94 500 66 S555 77 600 50 S655 64 700 25 V190 H0Z" fill="url(#salesFill)"/><path d="M0 142 C42 138 53 119 100 125 S157 147 200 108 S258 113 300 95 S354 111 400 73 S453 94 500 66 S555 77 600 50 S655 64 700 25" fill="none" stroke="#c65bff" stroke-width="3" vector-effect="non-scaling-stroke"/><path d="M0 162 C49 154 53 168 100 151 S151 162 200 140 S250 148 300 126 S350 142 400 109 S454 128 500 106 S553 113 600 84 S653 102 700 66" fill="none" stroke="#3dbff2" stroke-width="2" stroke-dasharray="5 5" vector-effect="non-scaling-stroke"/></svg><div class="admin-chart-x"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div></div>
          </div>
        </section>
        <section class="admin-surface admin-activity-card">
          <div class="admin-surface-heading"><div><h2>Recent activity</h2><p>Latest demo events</p></div><button class="admin-more-button" type="button" aria-label="More activity options"><i class="bi bi-three-dots"></i></button></div>
          <ul class="admin-activity-list">
            <li><span class="activity-icon activity-purple"><i class="bi bi-image"></i></span><span><strong>New artwork listed</strong><small>Starlight Drift · by cosmic.art</small></span><time>2m ago</time></li>
            <li><span class="activity-icon activity-blue"><i class="bi bi-person-plus"></i></span><span><strong>New user registered</strong><small>luna@example.test</small></span><time>18m ago</time></li>
            <li><span class="activity-icon activity-green"><i class="bi bi-bag-check"></i></span><span><strong>Demo purchase completed</strong><small>Moonlit Guardian · 1.24 ETH</small></span><time>1h ago</time></li>
            <li><span class="activity-icon activity-pink"><i class="bi bi-pencil-square"></i></span><span><strong>Artwork details updated</strong><small>Neon Wanderer</small></span><time>3h ago</time></li>
          </ul>
          <button class="admin-text-button" type="button" data-admin-nav="transactions">View all activity <i class="bi bi-arrow-right"></i></button>
        </section>
      </div>
      <section class="admin-surface admin-table-surface">
        <div class="admin-surface-heading"><div><h2>Recently listed</h2><p>Latest artworks submitted to the demo marketplace</p></div><button class="admin-text-button" type="button" data-admin-nav="nfts">View all NFTs <i class="bi bi-arrow-right"></i></button></div>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Artwork</th><th>Creator</th><th>Category</th><th>Price</th><th>Status</th></tr></thead><tbody id="overviewNftRows"></tbody></table></div>
      </section>
    </section>

    <section class="admin-panel" data-admin-panel="nfts" hidden>
      <div class="admin-page-heading"><div><p class="admin-overline">CATALOG / MANAGEMENT</p><h1>NFT management</h1><p>Add, edit, and review artwork listings in this local demo.</p></div><button class="admin-primary-button" type="button" data-open-nft><i class="bi bi-plus-lg"></i> Add new NFT</button></div>
      <section class="admin-surface admin-table-surface">
        <div class="admin-table-toolbar"><div class="admin-filter-tabs"><button class="active" type="button" data-nft-filter="all">All <span id="nftFilterAll">0</span></button><button type="button" data-nft-filter="Listed">Listed</button><button type="button" data-nft-filter="Draft">Draft</button><button type="button" data-nft-filter="Review">In review</button></div><label class="admin-inline-search"><i class="bi bi-search"></i><input id="nftSearch" type="search" placeholder="Search artwork" aria-label="Search artwork"></label></div>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Artwork</th><th>Creator</th><th>Category</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead><tbody id="nftRows"></tbody></table></div>
        <div class="admin-table-footer"><span id="nftTableCount">0 artworks</span><span>Demo data · changes are temporary</span></div>
      </section>
    </section>

    <section class="admin-panel" data-admin-panel="users" hidden>
      <div class="admin-page-heading"><div><p class="admin-overline">COMMUNITY / ACCOUNTS</p><h1>User management</h1><p>Review fictional accounts and their demo access status.</p></div><span class="admin-count-pill"><i class="bi bi-people"></i> 56 demo users</span></div>
      <section class="admin-surface admin-table-surface">
        <div class="admin-table-toolbar"><div><h2>Registered users</h2><p>Sample identities use the reserved example.test domain.</p></div><label class="admin-inline-search"><i class="bi bi-search"></i><input id="userSearch" type="search" placeholder="Search users" aria-label="Search users"></label></div>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>User</th><th>Email address</th><th>Joined</th><th>Role</th><th>Account</th><th>Actions</th></tr></thead><tbody id="userRows">
          <tr data-search="luna moon luna@example.test"><td><span class="admin-user-cell"><i class="user-dot user-lilac">L</i><strong>Luna Moon</strong></span></td><td>luna@example.test</td><td>Oct 12, 2026</td><td><span class="role-badge">Collector</span></td><td><span class="status-badge status-listed user-status">Active</span></td><td><button class="row-action user-toggle" type="button">Deactivate</button></td></tr>
          <tr data-search="kai star kai@example.test"><td><span class="admin-user-cell"><i class="user-dot user-blue">K</i><strong>Kai Star</strong></span></td><td>kai@example.test</td><td>Oct 10, 2026</td><td><span class="role-badge">Creator</span></td><td><span class="status-badge status-listed user-status">Active</span></td><td><button class="row-action user-toggle" type="button">Deactivate</button></td></tr>
          <tr data-search="mika pixel mika@example.test"><td><span class="admin-user-cell"><i class="user-dot user-pink">M</i><strong>Mika Pixel</strong></span></td><td>mika@example.test</td><td>Oct 08, 2026</td><td><span class="role-badge">Creator</span></td><td><span class="status-badge status-draft user-status">Inactive</span></td><td><button class="row-action user-toggle" type="button">Activate</button></td></tr>
          <tr data-search="ren orbit ren@example.test"><td><span class="admin-user-cell"><i class="user-dot user-green">R</i><strong>Ren Orbit</strong></span></td><td>ren@example.test</td><td>Oct 05, 2026</td><td><span class="role-badge">Collector</span></td><td><span class="status-badge status-listed user-status">Active</span></td><td><button class="row-action user-toggle" type="button">Deactivate</button></td></tr>
          <tr data-search="admin demo admin@example.test"><td><span class="admin-user-cell"><i class="user-dot user-gold">A</i><strong>Demo Admin</strong></span></td><td>admin@example.test</td><td>Oct 01, 2026</td><td><span class="role-badge role-admin">Admin</span></td><td><span class="status-badge status-listed user-status">Active</span></td><td><span class="admin-protected-label">Protected demo</span></td></tr>
        </tbody></table></div>
        <div class="admin-table-footer"><span>Showing 5 fictional users</span><span>Account changes only affect this page.</span></div>
      </section>
    </section>

    <section class="admin-panel" data-admin-panel="transactions" hidden>
      <div class="admin-page-heading"><div><p class="admin-overline">MARKETPLACE / ACTIVITY</p><h1>Transactions</h1><p>Illustrative purchase history with fictional buyers and prices.</p></div><span class="admin-count-pill"><i class="bi bi-arrow-left-right"></i> 32 simulated sales</span></div>
      <section class="admin-surface admin-table-surface">
        <div class="admin-table-toolbar"><div><h2>Demo purchase history</h2><p>These are sample records, not actual payments.</p></div><label class="admin-inline-search"><i class="bi bi-search"></i><input id="transactionSearch" type="search" placeholder="Search transactions" aria-label="Search transactions"></label></div>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Transaction</th><th>Artwork</th><th>Buyer</th><th>Price</th><th>Date</th><th>Status</th></tr></thead><tbody id="transactionRows">
          <tr data-search="px-24018 moonlit guardian luna moon"><td><span class="transaction-id">PX-24018</span></td><td><span class="admin-art-cell"><img src="{{ asset('assets/cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png') }}" alt="">Moonlit Guardian</span></td><td>Luna Moon</td><td class="admin-price">1.24 ETH</td><td>Oct 16, 2026</td><td><span class="status-badge status-listed">Complete</span></td></tr>
          <tr data-search="px-24017 neon wanderer kai star"><td><span class="transaction-id">PX-24017</span></td><td><span class="admin-art-cell"><img src="{{ asset('assets/anime-style-character-space.jpg') }}" alt="">Neon Wanderer</span></td><td>Kai Star</td><td class="admin-price">0.86 ETH</td><td>Oct 15, 2026</td><td><span class="status-badge status-listed">Complete</span></td></tr>
          <tr data-search="px-24016 aurora veil ren orbit"><td><span class="transaction-id">PX-24016</span></td><td><span class="admin-art-cell"><img src="{{ asset('assets/29108282_JEMA GER 1446-01.jpg') }}" alt="">Aurora Veil</span></td><td>Ren Orbit</td><td class="admin-price">1.64 ETH</td><td>Oct 14, 2026</td><td><span class="status-badge status-review">Pending</span></td></tr>
          <tr data-search="px-24015 orbit glow mika pixel"><td><span class="transaction-id">PX-24015</span></td><td><span class="admin-art-cell"><img src="{{ asset('assets/anime-style-character-space (2).jpg') }}" alt="">Orbit Glow</span></td><td>Mika Pixel</td><td class="admin-price">1.08 ETH</td><td>Oct 13, 2026</td><td><span class="status-badge status-listed">Complete</span></td></tr>
        </tbody></table></div>
        <div class="admin-table-footer"><span>Showing 4 sample transactions</span><span>Currency values are illustrative.</span></div>
      </section>
    </section>

    <section class="admin-panel" data-admin-panel="categories" hidden>
      <div class="admin-page-heading"><div><p class="admin-overline">CATALOG / ORGANIZATION</p><h1>Categories</h1><p>Organize demo artwork into browsable collections.</p></div><button class="admin-primary-button" type="button" id="addCategory"><i class="bi bi-plus-lg"></i> Add category</button></div>
      <section class="admin-category-grid" id="categoryGrid">
        <article class="admin-category-card"><span class="category-icon category-purple"><i class="bi bi-moon-stars"></i></span><div><h2>Space &amp; cosmic</h2><p>36 artworks · Active</p></div><button class="admin-more-button category-remove" type="button" aria-label="Remove Space and cosmic category"><i class="bi bi-three-dots"></i></button></article>
        <article class="admin-category-card"><span class="category-icon category-pink"><i class="bi bi-person-hearts"></i></span><div><h2>Anime</h2><p>28 artworks · Active</p></div><button class="admin-more-button category-remove" type="button" aria-label="Remove Anime category"><i class="bi bi-three-dots"></i></button></article>
        <article class="admin-category-card"><span class="category-icon category-blue"><i class="bi bi-joystick"></i></span><div><h2>Pixel art</h2><p>18 artworks · Active</p></div><button class="admin-more-button category-remove" type="button" aria-label="Remove Pixel art category"><i class="bi bi-three-dots"></i></button></article>
        <article class="admin-category-card"><span class="category-icon category-green"><i class="bi bi-stars"></i></span><div><h2>Characters</h2><p>22 artworks · Active</p></div><button class="admin-more-button category-remove" type="button" aria-label="Remove Characters category"><i class="bi bi-three-dots"></i></button></article>
      </section>
    </section>

    <section class="admin-panel" data-admin-panel="settings" hidden>
      <div class="admin-page-heading"><div><p class="admin-overline">WORKSPACE / PREFERENCES</p><h1>Settings</h1><p>Basic preferences for the local dashboard demo.</p></div></div>
      <section class="admin-surface admin-settings-card"><div class="admin-surface-heading"><div><h2>Workspace settings</h2><p>These preferences are for display only and are not saved to a server.</p></div></div>
        <label class="admin-setting-row"><span><strong>Workspace name</strong><small>Name shown in the dashboard sidebar.</small></span><input type="text" value="Pixmoree" aria-label="Workspace name"></label>
        <label class="admin-setting-row"><span><strong>Admin display name</strong><small>Name shown on your demo profile.</small></span><input type="text" value="Demo Admin" aria-label="Admin display name"></label>
        <label class="admin-setting-row"><span><strong>Demo notifications</strong><small>Show sample notification indicators.</small></span><input class="admin-switch" type="checkbox" checked aria-label="Demo notifications"></label>
        <div class="admin-setting-actions"><span>Changes are only visible in this browser session.</span><button type="button" class="admin-primary-button" id="saveSettings">Save preferences</button></div>
        <p class="admin-settings-feedback" id="settingsFeedback" role="status" hidden></p>
      </section>
    </section>
    <footer class="admin-footer"><span>PIXMOREE ADMIN DEMO</span><span>Made for creators and collectors <i class="bi bi-stars" aria-hidden="true"></i></span></footer>
  </main>
</div>

<dialog class="admin-dialog" id="nftDialog" aria-labelledby="nftDialogTitle">
  <form id="nftForm" class="admin-dialog-card">
    <div class="admin-dialog-heading"><div><p class="admin-overline">CATALOG / NEW LISTING</p><h2 id="nftDialogTitle">Add new NFT</h2></div><button class="admin-more-button" type="button" data-close-dialog aria-label="Close dialog"><i class="bi bi-x-lg"></i></button></div>
    <label>Artwork title<input name="title" type="text" placeholder="e.g. Starlight Dream" required></label>
    <label>Creator<input name="creator" type="text" placeholder="e.g. artist.name" required></label>
    <div class="admin-dialog-row"><label>Category<select name="category"><option>Space</option><option>Anime</option><option>Pixel art</option><option>Characters</option></select></label><label>Price (ETH)<input name="price" type="number" min="0" step="0.01" placeholder="0.00" required></label></div>
    <label>Listing status<select name="status"><option>Draft</option><option>Review</option><option>Listed</option></select></label>
    <label>Artwork image URL (optional)<input name="image" type="url" placeholder="https://example.test/artwork.png"></label>
    <p class="admin-dialog-note"><i class="bi bi-info-circle"></i> Demo listing only; changes are not saved to a database.</p>
    <div class="admin-dialog-actions"><button type="button" class="admin-secondary-button" data-close-dialog>Cancel</button><button class="admin-primary-button" type="submit">Add artwork</button></div>
  </form>
</dialog>
<div class="admin-toast" id="adminToast" role="status" aria-live="polite"></div>
<script src="{{ asset('js/admin.js') }}"></script>
</body>
</html>
