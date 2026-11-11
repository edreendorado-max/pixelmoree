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
            <a href="{{ route('store') }}"><i class="bi bi-compass" aria-hidden="true"></i> Store</a>
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
        <p>Review your account's legacy Pixel Coin balance and transaction history.</p>
    </header>

    <section class="pixel-wallet-summary" aria-labelledby="pixel-wallet-balance-heading">
        <div class="pixel-wallet-summary-label">
            <span class="pixel-wallet-summary-icon"><i class="bi bi-coin" aria-hidden="true"></i></span>
            <div>
                <small id="pixel-wallet-balance-heading">YOUR PIXEL COIN BALANCE</small>
                <strong><span data-pixelcoin-balance>0</span> <span>PIXEL COIN</span></strong>
                <p class="pixel-wallet-note">Pixel Coin is disabled during beta and has no cash value.</p>
            </div>
        </div>
        <div>
            <strong>Wallet actions unavailable</strong>
            <p class="pixel-wallet-note">Top-ups, purchases, and payouts are disabled until a payment provider is integrated.</p>
        </div>
        <p class="pixel-wallet-feedback" id="memberPixelWalletFeedback" role="status" aria-live="polite" hidden></p>
    </section>
    <section class="pixel-wallet-activity">
        <h2>PHP balance</h2>
        <p>Only admin-verified payments and completed resale proceeds are cleared. USD display is an estimate, not an exchange quote.</p>
        @php($cashPhp = ((int) ($account?->cash_balance_minor ?? 0)) / 100)
        @php($reservedPhp = ((int) ($account?->cash_reserved_minor ?? 0)) / 100)
        <strong>PHP {{ number_format($cashPhp, 2) }} · approximately USD {{ number_format($usdRate > 0 ? $cashPhp / $usdRate : 0, 2) }}</strong>
        <p>Reserved for pending withdrawals: PHP {{ number_format($reservedPhp, 2) }} · available: PHP {{ number_format(max(0, $cashPhp - $reservedPhp), 2) }}</p>
        @if (session('status'))<p role="status">{{ session('status') }}</p>@endif
        @if ($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
        <div class="pixel-wallet-safety" role="status">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <span>Top-ups, artwork purchases, and withdrawals are disabled during beta. Do not send funds or submit payment information.</span>
        </div>
    </section>

    <aside class="pixel-wallet-safety" aria-label="Payment information">
        <i class="bi bi-shield-check" aria-hidden="true"></i>
        <span><strong>No payment provider is integrated.</strong> Payment, payout, resale, and Pixel Coin checkout actions are disabled. Never enter a wallet PIN, password, or OTP on this page.</span>
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

    <section class="pixel-wallet-activity" aria-labelledby="cash-request-heading">
        <h2 id="cash-request-heading">Payment and withdrawal order history</h2>
        <div class="member-history-table-wrap">
            <table class="member-history-table">
                <thead><tr><th>Purchase ID</th><th>Type</th><th>Amount</th><th>Method / destination</th><th>Status</th><th>Payout reference</th></tr></thead>
                <tbody>
                    @forelse ($cashOrders as $order)
                        <tr>
                            <td>{{ $order->purchase_id }}</td>
                            <td>{{ str_replace('_', ' ', $order->type) }}</td>
                            <td>PHP {{ number_format($order->amount_minor / 100, 2) }}</td>
                            <td>{{ $order->payout_email ?: $order->payment_method }}</td>
                            <td>{{ str_replace('_', ' ', $order->status) }}</td>
                            <td>{{ $order->payout_reference ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="member-empty-state">No cash requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <section class="pixel-wallet-activity" aria-labelledby="cash-ledger-heading">
        <h2 id="cash-ledger-heading">Cash ledger</h2>
        <div class="member-history-table-wrap">
            <table class="member-history-table">
                <thead><tr><th>Activity</th><th>Change</th><th>Balance after</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse ($cashLedger as $entry)
                        <tr><td>{{ str_replace('_', ' ', $entry->type) }}</td><td>{{ $entry->direction === 'credit' ? '+' : '−' }} PHP {{ number_format($entry->amount_minor / 100, 2) }}</td><td>PHP {{ number_format($entry->balance_after_minor / 100, 2) }}</td><td>{{ \Illuminate\Support\Carbon::parse($entry->created_at)->format('M j, Y H:i') }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="member-empty-state">No cleared cash activity yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <a class="pixel-wallet-back" href="/user"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to account</a>
</main>
<script src="{{ asset('js/pixel-coin.js') }}?v=20261104-wallet"></script>
</body>
</html>