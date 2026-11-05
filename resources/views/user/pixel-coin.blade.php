<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pixel Coin wallet — Pixmoree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
</head>
<body class="pixel-coin-page">
<header class="profile-settings-topbar">
    <div class="profile-settings-topbar-inner">
        <a class="profile-settings-brand" href="/user" aria-label="Pixmoree account home">
            <img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree">
        </a>
        <nav class="profile-settings-nav" aria-label="Account navigation">
            <a href="/week12"><i class="bi bi-compass" aria-hidden="true"></i> Explore</a>
            <a class="profile-settings-back-link" href="/user"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to account</a>
            <a href="/user/pixel-coin" class="member-pixel-coin-link" aria-label="Pixel Coin wallet">
                <i class="bi bi-coin" aria-hidden="true"></i>
                <span data-pixelcoin-balance>0</span>
                <small>PIXEL</small>
            </a>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="profile-settings-logout" type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Log out</button>
            </form>
        </nav>
    </div>
</header>

<main class="pixel-wallet-main">
    <header class="pixel-wallet-heading">
        <p class="profile-settings-eyebrow"><i class="bi bi-coin" aria-hidden="true"></i> PIXMOREE ACCOUNT</p>
        <h1>Pixel Coin wallet</h1>
        <p>Manage your in-app demo credits and review wallet activity.</p>
    </header>

    <section class="pixel-wallet-summary" aria-labelledby="pixel-wallet-balance-heading">
        <div class="pixel-wallet-summary-label">
            <span class="pixel-wallet-summary-icon"><i class="bi bi-coin" aria-hidden="true"></i></span>
            <div>
                <small id="pixel-wallet-balance-heading">YOUR PIXEL COIN BALANCE</small>
                <strong><span data-pixelcoin-balance>0</span> <span>PIXEL COIN</span></strong>
                <p class="pixel-wallet-note">Free demo credits. No cash value or withdrawal.</p>
            </div>
        </div>
        <div>
            <strong>Add free demo credits</strong>
            <div class="pixel-wallet-options" aria-label="Demo credit amounts">
                <button type="button" data-pixel-topup="500" disabled><i class="bi bi-plus-lg" aria-hidden="true"></i> 500</button>
                <button type="button" data-pixel-topup="1000" disabled><i class="bi bi-plus-lg" aria-hidden="true"></i> 1,000</button>
                <button type="button" data-pixel-topup="2500" disabled><i class="bi bi-plus-lg" aria-hidden="true"></i> 2,500</button>
            </div>
            <p class="pixel-wallet-note">Credits are recorded to your account balance and ledger.</p>
        </div>
        <p class="pixel-wallet-feedback" id="memberPixelWalletFeedback" role="status" aria-live="polite" hidden></p>
    </section>

    <aside class="pixel-wallet-safety" aria-label="Payment information">
        <i class="bi bi-shield-check" aria-hidden="true"></i>
        <span><strong>Demo wallet only.</strong> No real GCash, PayPal, or prepaid-load payment is connected. No money is charged. Never enter a wallet PIN, password, or OTP on this page.</span>
    </aside>

    <section class="pixel-wallet-activity" aria-labelledby="pixel-wallet-activity-heading">
        <h2 id="pixel-wallet-activity-heading">Recent wallet activity</h2>
        <div class="member-history-table-wrap">
            <table class="member-history-table">
                <thead><tr><th>Activity</th><th>Change</th><th>Balance after</th><th>Date</th></tr></thead>
                <tbody id="memberPixelCoinActivity">
                    <tr><td colspan="4" class="member-empty-state">Loading wallet activity...</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <a class="pixel-wallet-back" href="/user"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to account</a>
</main>
<script src="{{ asset('js/pixel-coin.js') }}?v=20261104-wallet"></script>
</body>
</html>