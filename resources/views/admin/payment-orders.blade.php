<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payments review — Pixmoree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}?v=20261111-admin-theme" rel="stylesheet">
    <link href="{{ asset('css/week12.css') }}?v=20261111-admin-theme" rel="stylesheet">
    <link href="{{ asset('css/admin-content.css') }}?v=20261111-admin-theme" rel="stylesheet">
</head>
<body class="cms-admin-page payment-review-page">
<header class="cms-topbar">
    <div class="cms-topbar-inner">
        <a class="cms-brand" href="{{ route('week12.admin') }}">
            <img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree">
            <span>ADMIN STUDIO</span>
        </a>
        <nav class="cms-topnav" aria-label="Admin navigation">
            <a href="{{ route('week12.admin') }}">Overview</a>
            <a href="{{ route('week12.admin.artworks') }}">Artwork</a>
            <a href="{{ route('admin.users') }}">Users</a>
            <a href="{{ route('admin.payment-orders') }}" class="active" aria-current="page">Payments</a>
            <a href="{{ route('admin.content.edit', 'home') }}">Website content</a>
        </nav>
        <a class="cms-account" href="{{ route('store') }}">Store <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a>
    </div>
</header>
<main class="cms-admin-main payment-review-main">
<header class="payment-review-heading">
    <p class="cms-overline"><i class="bi bi-shield-lock" aria-hidden="true"></i> ADMIN STUDIO</p>
    <h1>Payment and resale review</h1>
</header>
@if (session('status'))<div class="alert w12-alert" role="status">{{ session('status') }}</div>@endif
<section class="payment-review-section" aria-labelledby="payment-requests-title">
<h2 id="payment-requests-title">Payment requests</h2>
@forelse ($orders as $order)
<article class="payment-review-card"><strong>{{ $order->type }} · PHP {{ number_format($order->amount_minor / 100, 2) }}</strong>
<p>Buyer: {{ $order->buyer?->email }} · Method: {{ $order->payment_method }} · Reference: {{ $order->transaction_reference }} · Status: {{ $order->status }}</p>
@if ($order->proof_path)<a href="{{ route('admin.payment-orders.proof', $order) }}">View private proof</a>@endif
@if ($order->type === 'withdrawal')
<p>PayPal destination: {{ $order->payout_email }} · Reserved PHP: {{ number_format($order->amount_minor / 100, 2) }}@if ($order->payout_reference) · Payout reference: {{ $order->payout_reference }}@endif</p>
@if ($order->status === 'pending_review' || $order->status === 'payout_approved')
<form class="payment-review-actions" method="post" action="{{ route('admin.payment-orders.review', $order) }}">@csrf
@if ($order->status === 'pending_review')<button class="w12-btn-outline" name="decision" value="approve">Approve request (reserve only; do not mark as paid)</button>@endif
@if ($order->status === 'payout_approved')
<label>Manual PayPal payout reference<input name="payout_reference" maxlength="120" required></label>
<button class="w12-btn-primary" name="decision" value="mark_paid">Record as paid after sending transfer</button>
@endif
<button class="w12-btn-danger" name="decision" value="reject">Reject and release reserve</button></form>
@endif
@elseif ($order->status === 'pending_review')
<form class="payment-review-actions" method="post" action="{{ route('admin.payment-orders.review', $order) }}">@csrf
<button class="w12-btn-primary" name="decision" value="approve">Approve after independently verifying receipt</button>
<button class="w12-btn-danger" name="decision" value="reject">Reject</button></form>
@endif</article>
@empty<p class="payment-review-empty">No payment requests.</p>@endforelse
</section>
<section class="payment-review-section" aria-labelledby="resale-review-title">
<h2 id="resale-review-title">Resale copyright review</h2>
@forelse ($resales as $resale)
<article class="payment-review-card"><strong>{{ $resale->title }}</strong><p>Seller: {{ $resale->seller_name }} · PHP {{ number_format($resale->price_minor / 100, 2) }} · {{ $resale->status }}</p>
<p><a href="{{ route('admin.resales.evidence', $resale->id) }}">View private ownership evidence</a> · {{ $resale->ownership_declaration }}</p>
@if ($resale->status === 'pending_review')<form class="payment-review-actions" method="post" action="{{ route('admin.resales.review', $resale->id) }}">@csrf<button class="w12-btn-primary" name="decision" value="approve">Approve listing</button><button class="w12-btn-danger" name="decision" value="reject">Reject</button></form>@endif</article>
@empty<p class="payment-review-empty">No resale submissions.</p>@endforelse
</section>
</main>
</body>
</html>
