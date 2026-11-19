<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout — Pixmoree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
    <link href="{{ asset('css/payment-checkout.css') }}" rel="stylesheet">
    <link href="{{ asset('css/typography.css') }}?v=20261117" rel="stylesheet">
</head>
<body class="payment-checkout-page">
<header class="store-header">
    <a class="store-brand" href="{{ route('store') }}"><img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree"></a>
    <nav aria-label="Main navigation">
        <a href="{{ route('store') }}">Store</a>
        <a href="{{ route('user.purchases') }}">Purchases</a>
    </nav>
</header>
<main class="payment-checkout-main">
    <a class="payment-checkout-back" href="{{ route('store') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to Store</a>
    <header class="payment-checkout-heading">
        <p class="store-eyebrow"><i class="bi bi-shield-check" aria-hidden="true"></i> MANUAL GCash CHECKOUT</p>
        <h1>Buy {{ $artwork->title }}</h1>
        <p>Pay the Pixmoree admin account by GCash. The admin verifies your receipt before assigning one unique edition item.</p>
    </header>
    @if (session('status'))<div class="payment-checkout-notice" role="status">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="payment-checkout-notice payment-checkout-error" role="alert">{{ $errors->first() }}</div>@endif
    <section class="payment-checkout-card">
        <div class="payment-checkout-summary">
            <img src="{{ asset(str_starts_with($artwork->image, 'storage/') ? $artwork->image : 'assets/' . $artwork->image) }}" alt="{{ $artwork->title }}">
            <div><small>Creator: {{ $seller->name }}</small><h2>{{ $artwork->title }}</h2><strong>PHP {{ number_format($amountMinor / 100, 2) }}</strong><p>Each purchase transfers one edition item with its own unique Item ID.</p></div>
        </div>
        <label class="payment-checkout-field" for="edition-item"><strong>Select one available item</strong>
            <select id="edition-item" name="item_id" form="gcash-purchase" required>
                @foreach ($items as $item)<option value="{{ $item->item_id }}">Edition {{ $item->edition_number }} · {{ $item->item_id }}</option>@endforeach
            </select>
        </label>
        @if ($hasAdminQr)
        <form id="gcash-purchase" method="post" action="{{ route('user.purchase-orders.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="artwork_id" value="{{ $artwork->id }}">
            <input type="hidden" name="payment_method" value="gcash">
            <fieldset class="payment-checkout-methods"><legend>Pay the Pixmoree admin by GCash</legend>
                <p>Account: {{ $adminPayment->gcash_account_name }} · GCash: {{ $adminPayment->gcash_number }}</p>
                <img class="admin-payment-qr" src="{{ route('user.checkout.qr', ['artworkId' => $artwork->id, 'method' => 'gcash']) }}" alt="Pixmoree admin GCash QR code">
                <p>Send exactly PHP {{ number_format($amountMinor / 100, 2) }}. The creator receives {{ \App\Support\PixelCoinLedger::format($coinAmount) }} Pixel Coins after admin approval.</p>
            </fieldset>
            <label class="payment-checkout-field" for="transaction-reference">Transaction / receipt number
                <input id="transaction-reference" name="transaction_reference" type="text" value="{{ old('transaction_reference') }}" maxlength="120" autocomplete="off" required>
            </label>
            <label class="payment-checkout-field" for="payment-proof">Upload payment receipt or screenshot
                <input id="payment-proof" name="proof" type="file" accept="image/jpeg,image/png,image/webp" required>
                <small>JPG, PNG, or WebP; maximum 5 MB. Do not upload screenshots containing your PIN or OTP.</small>
            </label>
            <p class="payment-checkout-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i> Receipt checks are manual; there is no automatic payment-verification API. The admin will confirm the receipt and exact amount before ownership is transferred. Do not submit a payment you have not made.</p>
            <button class="store-buy-button" type="submit">Submit GCash receipt for review</button>
        </form>
        @else
            <p class="payment-checkout-notice" role="status">Admin GCash instructions are not configured yet. Ask an administrator to upload the QR code before paying.</p>
        @endif
        <form id="coin-purchase" method="post" action="{{ route('user.purchase-orders.store') }}">
            @csrf<input type="hidden" name="artwork_id" value="{{ $artwork->id }}"><input type="hidden" name="payment_method" value="pixel_coin">
            <label class="payment-checkout-field">Choose the edition item
                <select name="item_id" required>@foreach ($items as $item)<option value="{{ $item->item_id }}">Edition {{ $item->edition_number }} · {{ $item->item_id }}</option>@endforeach</select>
            </label>
            <p>Pay {{ \App\Support\PixelCoinLedger::format($coinAmount) }} Pixel Coins. Creator receives the same amount directly in their Pixmoree balance.</p>
            <button class="store-buy-button" type="submit">Buy with Pixel Coins</button>
        </form>
    </section>
</main>
</body>
</html>
