<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $order->type === 'creator_payout' ? 'Withdrawal receipt' : 'Purchase receipt' }} | Pixmoree</title>
    <link href="{{ asset('css/site.css') }}?v=20261127-creator-ui" rel="stylesheet">
    <link href="{{ asset('css/receipt.css') }}?v=20261122" rel="stylesheet">
</head>
<body class="receipt-page">
    @php($isWithdrawal = $order->type === 'creator_payout')
    @php($isResale = ! $isWithdrawal && $order->artwork && (int) $order->seller_id !== (int) $order->artwork->user_id)
    <nav class="receipt-actions" aria-label="Receipt actions">
        <a href="{{ $isWithdrawal ? route('user.earnings') : route('user.purchases') }}">&larr; {{ $isWithdrawal ? 'Back to earnings' : 'Back to purchases' }}</a>
        <button type="button" onclick="window.print()">Print or save as PDF</button>
    </nav>

    <main class="receipt-main">
        <article class="receipt-card">
            <header class="receipt-heading">
                <p class="receipt-brand">PIXMOREE <span>TRANSACTION RECORD</span></p>
                <p class="receipt-simulation">SIMULATION RECORD</p>
                <h1>{{ $isWithdrawal ? 'Withdrawal receipt' : ($isResale ? 'Resale purchase receipt' : 'Purchase receipt') }}</h1>
                <p>This receipt records an in-app account update.</p>
            </header>

            <dl class="receipt-details">
                <div><dt>Receipt ID</dt><dd class="receipt-id">{{ $order->purchase_id }}</dd></div>
                <div><dt>Status</dt><dd>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</dd></div>
                <div><dt>Date and time</dt><dd>{{ $order->created_at?->format('M j, Y g:i A') ?? 'Not available' }}</dd></div>
                <div><dt>{{ $isWithdrawal ? 'Account' : 'Buyer' }}</dt><dd>{{ $order->buyer?->name ?? 'Account unavailable' }}</dd></div>
                @if ($isWithdrawal)
                    <div><dt>Transaction</dt><dd>Creator withdrawal record</dd></div>
                @else
                    <div><dt>Transaction type</dt><dd>{{ $isResale ? 'Resale purchase' : 'Creator purchase' }}</dd></div>
                    <div><dt>Artwork</dt><dd>{{ $order->artwork?->title ?? 'Artwork unavailable' }}</dd></div>
                    <div><dt>Edition</dt><dd>{{ $editionNumber ? number_format((int) $editionNumber) : 'Not available' }}</dd></div>
                    <div><dt>Unique item ID</dt><dd class="receipt-id">{{ $order->item_id ?? 'Not available' }}</dd></div>
                    @if ($order->payment_provider)
                        <div><dt>Payment account</dt><dd>{{ ['bank_transfer' => 'Bank transfer', 'gcash' => 'GCash', 'maya' => 'Maya', 'card' => 'Card'][$order->payment_provider] ?? 'Other' }}</dd></div>
                    @endif
                    @if ($order->simulation_reference)
                        <div><dt>Simulation reference</dt><dd class="receipt-id">{{ $order->simulation_reference }}</dd></div>
                    @endif
                @endif
                <div><dt>{{ $isWithdrawal ? 'Recorded withdrawal' : 'Recorded amount' }}</dt><dd class="receipt-amount">PHP {{ number_format($order->amount_minor / 100, 2) }}</dd></div>
            </dl>

            @if (! $isWithdrawal)
                <section class="receipt-transfer" aria-labelledby="receipt-transfer-heading">
                    <h2 id="receipt-transfer-heading">Edition ownership transfer</h2>
                    <p class="receipt-transfer-edition">Edition {{ $editionNumber ? number_format((int) $editionNumber) : '—' }} · Item ID {{ $order->item_id ?? 'Not available' }}</p>
                    <div class="receipt-transfer-flow">
                        <div><small>FROM</small><strong>{{ $transferFrom?->name ?? 'Previous owner unavailable' }}</strong></div>
                        <span aria-hidden="true">&rarr;</span>
                        <div><small>TO</small><strong>{{ $transferTo?->name ?? 'New owner unavailable' }}</strong></div>
                    </div>
                    @if ($ownershipTransfer)
                        <p class="receipt-transfer-confirmation">Transfer recorded for this purchase.</p>
                    @else
                        <p class="receipt-transfer-confirmation">The order lists this buyer and seller. A separate transfer record is unavailable.</p>
                    @endif
                </section>
            @endif

            <footer class="receipt-footer">
                <p>No payment or payout was processed. The amount is a demonstration value stored in Pixmoree.</p>
                @unless ($isWithdrawal)
                    <p>The edition item ownership was updated in the Pixmoree collection. No blockchain asset or copyright was transferred.</p>
                @endunless
            </footer>
        </article>
    </main>
</body>
</html>
