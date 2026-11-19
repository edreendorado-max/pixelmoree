<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pixel Coins — Pixmoree</title><link href="{{ asset('css/site.css') }}" rel="stylesheet"><link href="{{ asset('css/payment-checkout.css') }}" rel="stylesheet"><link href="{{ asset('css/typography.css') }}" rel="stylesheet"></head>
<body class="payment-checkout-page"><header class="store-header"><a class="store-brand" href="{{ route('user') }}">Pixmoree</a><nav><a href="{{ route('store') }}">Store</a><a href="{{ route('user.profile-settings') }}">Profile settings</a></nav></header>
<main class="payment-checkout-main"><a class="payment-checkout-back" href="{{ route('user') }}">← Back to account</a><header class="payment-checkout-heading"><p class="store-eyebrow">ACCOUNT WALLET</p><h1>Pixel Coins</h1><p>Available: <strong>{{ \App\Support\PixelCoinLedger::format((int) $user->pixel_coin_balance) }} PC</strong> · Reserved for withdrawals: {{ \App\Support\PixelCoinLedger::format((int) $user->pixel_coin_reserved) }} PC</p></header>
@if (session('status'))<div class="payment-checkout-notice" role="status">{{ session('status') }}</div>@endif @if ($errors->any())<div class="payment-checkout-notice payment-checkout-error" role="alert">{{ $errors->first() }}</div>@endif
@if (! $paymentsEnabled)<p class="payment-checkout-notice" role="status">Payments are disabled while the site is being configured.</p>@endif
<section class="payment-checkout-card"><h2>Top up by GCash</h2><p>Minimum ₱{{ number_format((float) $minimumTopupPhp, 2) }}. At the current rates, a ₱200 base top-up awards up to {{ \App\Support\PixelCoinLedger::format((int) $coinsForPhp200) }} PC. A {{ number_format((float) $topupFeePercent, 2) }}% admin fee is added to the amount paid. The admin verifies your receipt before coins are credited.</p>
@if (! $paymentsEnabled)
<p class="payment-checkout-notice">Top-ups are unavailable until payments are enabled.</p>
@elseif ($hasAdminQr)
<p>Account: {{ $adminPayment?->gcash_account_name }} · GCash: {{ $adminPayment?->gcash_number }}</p><img class="admin-payment-qr" src="{{ route('user.pixel-coins.qr') }}" alt="Pixmoree admin GCash QR code">
<form method="post" action="{{ route('user.pixel-coins.topups') }}" enctype="multipart/form-data">@csrf
<label class="payment-checkout-field">Top-up amount before fee (PHP)<input name="amount_php" type="number" min="{{ $minimumTopupPhp }}" max="100000" step="0.01" value="{{ old('amount_php', $minimumTopupPhp) }}" required></label>
<label class="payment-checkout-field">GCash transaction reference<input name="transaction_reference" maxlength="120" required></label>
<label class="payment-checkout-field">Receipt screenshot<input name="proof" type="file" accept="image/jpeg,image/png,image/webp" required><small>JPG, PNG, WebP; max 5 MB. Do not include PINs or OTPs.</small></label>
<p>Pay the base top-up amount plus the {{ number_format((float) $topupFeePercent, 2) }}% fee shown in checkout; save the receipt and exact reference.</p><button class="store-buy-button">Submit top-up for review</button></form>
@else<p class="payment-checkout-notice">Admin GCash instructions are not configured.</p>@endif
</section>
<section class="payment-checkout-card"><h2>Withdraw to GCash</h2><p>Rate: 1 PC = ₱{{ number_format((float) config('payments.pixel_coin_payout_php_per_coin', 1.2), 2) }}. Minimum withdrawal: {{ \App\Support\PixelCoinLedger::format((int) round((float) config('payments.pixel_coin_min_withdrawal', 200) * 100)) }} PC. Your balance is reserved while the admin sends the payout; rejection returns the coins.</p>
@if ($paymentsEnabled)
<form method="post" action="{{ route('user.pixel-coins.withdrawals') }}">@csrf<label class="payment-checkout-field">Pixel Coins to withdraw<input name="coin_amount" type="number" min="{{ config('payments.pixel_coin_min_withdrawal', 200) }}" max="1000000" step="0.01" required></label><p>Registered payout number: {{ $user->gcash_number ?: 'Not set' }}. Update your GCash number and private payout QR in Profile settings before submitting.</p><button class="store-buy-button">Request withdrawal</button></form></section>
@else<p class="payment-checkout-notice">Withdrawals are unavailable until payments are enabled.</p></section>@endif
<section class="payment-checkout-card"><h2>Recent top-ups and withdrawals</h2>@forelse ($orders as $order)<article><strong>{{ $order->type === 'pixel_coin_topup' ? 'Top-up' : 'Withdrawal' }} · {{ $order->status }}</strong><p>Record {{ $order->purchase_id }} · {{ \App\Support\PixelCoinLedger::format((int) $order->coin_amount) }} PC · PHP {{ number_format($order->amount_minor / 100, 2) }} @if ($order->fee_amount_minor) (includes PHP {{ number_format($order->fee_amount_minor / 100, 2) }} fee) @endif</p></article>@empty<p>No top-ups or withdrawals yet.</p>@endforelse</section>
<section class="payment-checkout-card"><h2>Pixel Coin ledger</h2>@forelse ($transactions as $transaction)<article><strong>{{ ucfirst(str_replace('_', ' ', $transaction->type)) }} · {{ $transaction->direction }}</strong><p>{{ $transaction->description }} · {{ \App\Support\PixelCoinLedger::format((int) $transaction->amount) }} PC · Balance {{ \App\Support\PixelCoinLedger::format((int) $transaction->balance_after) }} PC</p><small>Transaction ID: {{ $transaction->transaction_id }}</small></article>@empty<p>No ledger entries.</p>@endforelse</section>
<script>
const topupInput = document.querySelector('input[name="amount_php"]');
if (topupInput) {
  const feePercent = {{ (float) $topupFeePercent }};
  const coinNumerator = {{ (int) config('payments.pixel_coin_topup_numerator', 5) }};
  const coinDenominator = {{ (int) config('payments.pixel_coin_topup_denominator', 6) }};
  const payoutRate = {{ (float) config('payments.pixel_coin_payout_php_per_coin', 1.2) }};
  const summary = document.createElement('p');
  summary.setAttribute('aria-live', 'polite');
  topupInput.closest('label').after(summary);
  const updateTopup = () => {
    const base = Math.round((Number(topupInput.value) || 0) * 100);
    const fee = Math.round(base * feePercent / 100);
    const coins = Math.min(Math.floor(base * coinNumerator / coinDenominator), Math.floor(base / payoutRate));
    summary.textContent = `Base PHP ${(base / 100).toFixed(2)} + fee PHP ${(fee / 100).toFixed(2)} = pay PHP ${((base + fee) / 100).toFixed(2)} · credits ${(coins / 100).toFixed(2)} PC after approval.`;
  };
  topupInput.addEventListener('input', updateTopup);
  updateTopup();
}
</script></main></body></html>
