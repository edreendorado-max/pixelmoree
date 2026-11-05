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
    <section class="pixel-wallet-activity">
        <h2>Real-money PHP balance</h2>
        <p>Only admin-verified payments and completed resale proceeds are cleared. USD display is an estimate, not an exchange quote.</p>
        @php($cashPhp = ((int) ($account?->cash_balance_minor ?? 0)) / 100)
        @php($reservedPhp = ((int) ($account?->cash_reserved_minor ?? 0)) / 100)
        <strong>PHP {{ number_format($cashPhp, 2) }} · approximately USD {{ number_format($usdRate > 0 ? $cashPhp / $usdRate : 0, 2) }}</strong>
        <p>Reserved for pending withdrawals: PHP {{ number_format($reservedPhp, 2) }} · available: PHP {{ number_format(max(0, $cashPhp - $reservedPhp), 2) }}</p>
        @if (session('status'))<p role="status">{{ session('status') }}</p>@endif
        @if ($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
        <p><strong>Payment destination instructions</strong></p>
        <ul>
            @foreach (['gcash' => 'GCash', 'maya' => 'Maya', 'paypal' => 'PayPal'] as $method => $label)
                @if (config("payments.instructions.{$method}"))
                    <li>{{ $label }}: {{ config("payments.instructions.{$method}") }}</li>
                @endif
            @endforeach
        </ul>
        @if (collect(config('payments.instructions', []))->filter()->isEmpty())
            <p>No payment destination has been configured. Do not send money; the forms only record submissions for manual review.</p>
        @endif
        <form method="post" action="{{ route('user.cash.topups') }}" enctype="multipart/form-data">@csrf
            <label>Top up PHP <select name="amount_php"><option>100</option><option>500</option><option>1000</option></select></label>
            <label>Method <select name="payment_method"><option value="gcash">GCash</option><option value="maya">Maya</option><option value="paypal">PayPal</option></select></label>
            <label>Transaction reference <input name="transaction_reference" required maxlength="120"></label>
            <label>Proof <input name="proof" type="file" accept="image/png,image/jpeg" required></label><button>Submit for verification</button>
        </form>
        <form method="post" action="{{ route('user.pixel.prepaid-topups') }}" enctype="multipart/form-data">@csrf
            <h3>Prepaid load → Pixel Coin only</h3><p>Conversion: PHP 1 = {{ number_format((int) config('payments.pixel_coin_per_php', 100)) }} PIXEL COIN. Cannot be used for NFT checkout as a prepaid-load payment.</p><label>Amount PHP <select name="amount_php"><option>50</option><option>100</option><option>300</option></select></label>
            <label>Reference <input name="transaction_reference" required maxlength="120"></label><label>Proof <input name="proof" type="file" accept="image/png,image/jpeg" required></label><button>Submit prepaid-load top-up</button>
        </form>
        <form method="post" action="{{ route('user.cash.withdrawals') }}">@csrf
            <h3>Request a PayPal withdrawal</h3>
            <p>Requests reserve cleared funds. Admin approval does not send money; the request is marked paid only after a manual PayPal transfer is made and its payout reference recorded.</p>
            <label>Amount PHP <input name="amount_php" type="number" min="100" max="100000" step="1" required></label>
            <label>PayPal account email <input name="paypal_email" type="email" maxlength="254" required></label>
            <button type="submit">Request withdrawal</button>
        </form>
    </section>

    <aside class="pixel-wallet-safety" aria-label="Payment information">
        <i class="bi bi-shield-check" aria-hidden="true"></i>
        <span><strong>No payment provider is integrated.</strong> Pixmoree does not charge a payment method; any transfer is made externally and reviewed manually. Never enter a wallet PIN, password, or OTP on this page.</span>
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