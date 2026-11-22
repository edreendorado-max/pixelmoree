<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Transaction receipt - Pixmoree</title><link href="{{ asset('css/site.css') }}?v=20261127-creator-ui" rel="stylesheet"></head>
<body class="profile-settings-page"><main class="profile-settings-main">
    @php($isWithdrawal = $order->type === 'creator_payout')
    <section class="profile-settings-panel">
        <p class="profile-settings-eyebrow">PIXMOREE · TRANSACTION RECORD</p><h1>{{ $isWithdrawal ? 'Withdrawal receipt' : 'Purchase receipt' }}</h1>
        <p>Receipt ID: <strong>{{ $order->purchase_id }}</strong></p>
        @if ($order->artwork_id)
            <p>Artwork: <strong>{{ $order->artwork?->title ?? 'Artwork unavailable' }}</strong></p>
            <p>Edition item: <strong>{{ $order->item_id ?? '—' }}</strong></p>
        @else
            <p>Transaction: <strong>Creator withdrawal record</strong></p>
        @endif
        <p>Account: {{ $order->buyer?->name }}@if ($order->seller) · Seller: {{ $order->seller->name }}@endif</p>
        <p>{{ $isWithdrawal ? 'Withdrawal amount' : 'Purchase amount' }}: PHP {{ number_format($order->amount_minor / 100, 2) }}</p>
        <p>Status: {{ str_replace('_', ' ', $order->status) }} · {{ $order->created_at?->format('M j, Y g:i A') }}</p>
        <p>No payment or payout was processed. This record confirms the in-app {{ $isWithdrawal ? 'withdrawal entry.' : 'order and edition ownership transfer.' }}</p>
        <button type="button" onclick="window.print()">Print / Save receipt</button>
        <a href="{{ $isWithdrawal ? route('user.earnings') : route('user.purchases') }}">{{ $isWithdrawal ? 'Back to earnings' : 'Back to purchases' }}</a>
    </section>
</main></body></html>
