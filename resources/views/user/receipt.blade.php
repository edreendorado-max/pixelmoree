<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Purchase receipt — Pixmoree</title><link href="{{ asset('css/site.css') }}" rel="stylesheet"></head>
<body class="profile-settings-page"><main class="profile-settings-main">
    <section class="profile-settings-panel">
        <p class="profile-settings-eyebrow">PIXMOREE · SIMULATED TRANSACTION</p><h1>Purchase receipt</h1>
        <p>Receipt ID: <strong>{{ $order->purchase_id }}</strong></p>
        @if ($order->artwork_id)<p>Artwork: <strong>{{ $order->artwork?->title ?? 'Artwork unavailable' }}</strong></p><p>Edition item: <strong>{{ $order->item_id ?? '—' }}</strong></p>@else<p>Transaction: <strong>{{ str_replace('_', ' ', $order->type) }}</strong></p><p>Pixel Coins: <strong>{{ \App\Support\PixelCoinLedger::format((int) $order->coin_amount) }} PC</strong></p>@endif
        <p>Account: {{ $order->buyer?->name }}@if ($order->seller) · Seller: {{ $order->seller->name }}@endif</p>
        <p>Amount: PHP {{ number_format($order->amount_minor / 100, 2) }} · Method: {{ $order->payment_method }}</p>
        <p>Status: {{ str_replace('_', ' ', $order->status) }} · {{ $order->created_at?->format('M j, Y g:i A') }}</p>
        <p>No real payment was processed. This receipt records the in-app simulation and edition ownership transfer.</p>
        <button type="button" onclick="window.print()">Print / Save receipt</button> <a href="{{ route('user.purchases') }}">Back to purchases</a>
    </section>
</main></body></html>
