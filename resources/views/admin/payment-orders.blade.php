<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Transaction records - Pixmoree Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}?v=20261127-creator-ui" rel="stylesheet"><link href="{{ asset('css/week12.css') }}?v=20261127-creator-ui" rel="stylesheet"><link href="{{ asset('css/admin-content.css') }}" rel="stylesheet"><link href="{{ asset('css/typography.css') }}?v=20261127-creator-ui" rel="stylesheet">
</head>
<body class="cms-admin-page payment-review-page">
<header class="cms-topbar"><div class="cms-topbar-inner"><a class="cms-brand" href="{{ route('week12.admin') }}"><img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree"><span>ADMIN STUDIO</span></a><nav class="cms-topnav" aria-label="Admin navigation"><a href="{{ route('week12.admin') }}">Overview</a><a href="{{ route('week12.admin.artworks') }}">Artwork</a><a href="{{ route('admin.users') }}">Users</a><a href="{{ route('admin.analytics') }}">Analytics</a><a href="{{ route('admin.payment-orders') }}" class="active" aria-current="page">Transactions</a><a href="{{ route('admin.content.edit', 'home') }}">Website content</a></nav><span class="cms-account cms-admin-label">Administrator</span></div></header>
<main class="cms-admin-main payment-review-main">
    <header class="payment-review-heading"><p class="cms-overline"><i class="bi bi-shield-lock" aria-hidden="true"></i> ADMIN STUDIO</p><h1>Transaction records</h1><p>Orders and withdrawal entries are stored in the app. No external payment or payout is processed.</p></header>
    @if (session('status'))<div class="cms-alert cms-alert-success" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i>{{ session('status') }}</div>@endif
    <section class="payment-review-section"><h2>NFT purchases</h2>
        @forelse ($orders as $order)
            <article class="payment-review-card"><strong>{{ $order->artwork?->title ?? 'Artwork unavailable' }} · PHP {{ number_format($order->amount_minor / 100, 2) }}</strong>
                <p>Receipt: {{ $order->purchase_id }} · Edition: {{ $order->item_id }}<br>Buyer: {{ $order->buyer?->name }} ({{ $order->buyer?->email }}) · Seller: {{ $order->seller?->name ?? 'Unavailable' }}<br>Recorded creator amount: PHP {{ number_format($order->amount_minor / 100, 2) }} · {{ ucfirst($order->status) }} · {{ $order->created_at?->format('M j, Y g:i A') }}</p>
            </article>
        @empty<p class="payment-review-empty">No NFT orders have been recorded.</p>@endforelse
    </section>
    <section class="payment-review-section"><h2>Creator withdrawals</h2>
        @forelse ($payouts as $order)
            <article class="payment-review-card"><strong>{{ $order->buyer?->name ?? 'Account unavailable' }} · PHP {{ number_format($order->amount_minor / 100, 2) }}</strong>
                <p>Receipt: {{ $order->purchase_id }} · {{ ucfirst($order->status) }} · {{ $order->created_at?->format('M j, Y g:i A') }}<br>No cash payout was made.</p>
            </article>
        @empty<p class="payment-review-empty">No creator withdrawals have been recorded.</p>@endforelse
    </section>
    <section class="payment-review-section"><h2>Resale copyright review</h2><p>Resale moderation is a separate review task. Approval publishes a listing; it does not verify copyright ownership.</p>
        @forelse ($resales as $resale)
            <article class="payment-review-card"><strong>{{ $resale->title }}</strong><p>Seller: {{ $resale->seller_name }} · {{ number_format((int) $resale->quantity) }} item(s) · PHP {{ number_format($resale->price_minor / 100, 2) }} each · {{ ucfirst(str_replace('_', ' ', $resale->status)) }}</p><p><a href="{{ route('admin.resales.evidence', $resale->id) }}">View private ownership evidence</a> · {{ $resale->ownership_declaration }}</p>
                @if ($resale->status === 'pending_review')<form class="payment-review-actions" method="post" action="{{ route('admin.resales.review', $resale->id) }}">@csrf<button class="w12-btn-primary" name="decision" value="approve">Approve listing</button><button class="w12-btn-danger" name="decision" value="reject">Reject</button></form>@endif
            </article>
        @empty<p class="payment-review-empty">No resale submissions.</p>@endforelse
    </section>
</main>
</body></html>
