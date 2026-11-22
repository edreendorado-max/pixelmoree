<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout - Pixmoree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}?v=20261127-creator-ui" rel="stylesheet">
    <link href="{{ asset('css/payment-checkout.css') }}" rel="stylesheet">
    <link href="{{ asset('css/typography.css') }}?v=20261127-creator-ui" rel="stylesheet">
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
        <p class="store-eyebrow"><i class="bi bi-shield-check" aria-hidden="true"></i> ORDER CHECKOUT</p>
        <h1>Buy {{ $artwork->title }}</h1>
        <p>Confirm your order to receive a receipt and add this edition item to your collection.</p>
    </header>
    @if (session('status'))<div class="payment-checkout-notice" role="status">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="payment-checkout-notice payment-checkout-error" role="alert">{{ $errors->first() }}</div>@endif
    <section class="payment-checkout-card">
        <div class="payment-checkout-summary">
            <img src="{{ asset(str_starts_with($artwork->image, 'storage/') ? $artwork->image : 'assets/' . $artwork->image) }}" alt="{{ $artwork->title }}">
            <div><small>Creator: {{ $seller->name }}</small><h2>{{ $artwork->title }}</h2><strong>PHP {{ number_format($amountMinor / 100, 2) }}</strong><p>Each purchase transfers one edition item with its own unique Item ID.</p></div>
        </div>
        <form id="simulation-purchase" method="post" action="{{ route('user.purchase-orders.store') }}">
            @csrf<input type="hidden" name="artwork_id" value="{{ $artwork->id }}"><input type="hidden" name="payment_method" value="simulation">
            <label class="payment-checkout-field">Choose the edition item
                <select name="item_id" required>@foreach ($items as $item)<option value="{{ $item->item_id }}">Edition {{ $item->edition_number }} · {{ $item->item_id }}</option>@endforeach</select>
            </label>
            <p class="payment-checkout-notice" role="note">No payment is processed and no blockchain asset is transferred. This order updates your Pixmoree account records only.</p>
            <button class="store-buy-button" type="submit">Place order · PHP {{ number_format($amountMinor / 100, 2) }}</button>
        </form>
    </section>
</main>
</body>
</html>
