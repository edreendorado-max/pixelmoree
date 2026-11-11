<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout — Pixmoree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
    <link href="{{ asset('css/payment-checkout.css') }}" rel="stylesheet">
    <link href="{{ asset('css/typography.css') }}?v=20261111" rel="stylesheet">
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
        <p class="store-eyebrow"><i class="bi bi-shield-check" aria-hidden="true"></i> CREATOR-DIRECT CHECKOUT</p>
        <h1>Buy {{ $artwork->title }}</h1>
        <p>Pay {{ $seller->name }} directly by GCash or Maya. Pixmoree does not hold your payment.</p>
    </header>
    @if (session('status'))<div class="payment-checkout-notice" role="status">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="payment-checkout-notice payment-checkout-error" role="alert">{{ $errors->first() }}</div>@endif
    <section class="payment-checkout-card">
        <div class="payment-checkout-summary">
            <img src="{{ asset(str_starts_with($artwork->image, 'storage/') ? $artwork->image : 'assets/' . $artwork->image) }}" alt="{{ $artwork->title }}">
            <div><small>Pay to {{ $seller->name }}</small><h2>{{ $artwork->title }}</h2><strong>PHP {{ number_format($amountMinor / 100, 2) }}</strong><p>Calculated from the current listing price. Send the exact amount shown.</p></div>
        </div>
        @if ($paymentMethods === [])
            <p class="payment-checkout-notice" role="status">This creator has not configured a GCash or Maya QR code yet. Checkout is unavailable for now; please try again later.</p>
        @else
        <form method="post" action="{{ route('user.purchase-orders.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="artwork_id" value="{{ $artwork->id }}">
            <fieldset class="payment-checkout-methods">
                <legend>Choose payment method</legend>
                @foreach ($paymentMethods as $method)
                    <label class="payment-checkout-method">
                        <input type="radio" name="payment_method" value="{{ $method }}" @checked(old('payment_method') === $method || ($loop->first && old('payment_method') === null)) required>
                        <span>{{ strtoupper($method) }} QR</span>
                        <img src="{{ route('user.checkout.qr', ['artworkId' => $artwork->id, 'method' => $method]) }}" alt="{{ strtoupper($method) }} payment QR for {{ $seller->name }}">
                    </label>
                @endforeach
            </fieldset>
            <label class="payment-checkout-field" for="transaction-reference">Transaction / receipt number
                <input id="transaction-reference" name="transaction_reference" type="text" value="{{ old('transaction_reference') }}" maxlength="120" autocomplete="off" required>
            </label>
            <label class="payment-checkout-field" for="payment-proof">Upload payment receipt or screenshot
                <input id="payment-proof" name="proof" type="file" accept="image/jpeg,image/png,image/webp" required>
                <small>JPG, PNG, or WebP; maximum 5 MB. Do not upload screenshots containing your PIN or OTP.</small>
            </label>
            <p class="payment-checkout-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i> Receipt checks are manual; there is no automatic payment-verification API. The admin will confirm the receipt and exact amount before ownership is transferred. Do not submit a payment you have not made.</p>
            <button class="store-buy-button" type="submit">Submit receipt for review</button>
        </form>
        @endif
    </section>
</main>
</body>
</html>
