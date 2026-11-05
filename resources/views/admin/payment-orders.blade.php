<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Payments review — Pixmoree</title><link href="{{ asset('css/site.css') }}" rel="stylesheet"></head>
<body class="member-area"><main class="container py-4">
<h1>Payment and resale review</h1>
@if (session('status'))<p role="status">{{ session('status') }}</p>@endif
<h2>Payment requests</h2>
@forelse ($orders as $order)
<article class="member-panel-card mb-3"><strong>{{ $order->type }} · PHP {{ number_format($order->amount_minor / 100, 2) }}</strong>
<p>Buyer: {{ $order->buyer?->email }} · Method: {{ $order->payment_method }} · Reference: {{ $order->transaction_reference }} · Status: {{ $order->status }}</p>
@if ($order->proof_path)<a href="{{ route('admin.payment-orders.proof', $order) }}">View private proof</a>@endif
@if ($order->type === 'withdrawal')
<p>PayPal destination: {{ $order->payout_email }} · Reserved PHP: {{ number_format($order->amount_minor / 100, 2) }}@if ($order->payout_reference) · Payout reference: {{ $order->payout_reference }}@endif</p>
@if ($order->status === 'pending_review' || $order->status === 'payout_approved')
<form method="post" action="{{ route('admin.payment-orders.review', $order) }}">@csrf
@if ($order->status === 'pending_review')<button name="decision" value="approve">Approve request (reserve only; do not mark as paid)</button>@endif
@if ($order->status === 'payout_approved')
<label>Manual PayPal payout reference<input name="payout_reference" maxlength="120" required></label>
<button name="decision" value="mark_paid">Record as paid after sending transfer</button>
@endif
<button name="decision" value="reject">Reject and release reserve</button></form>
@endif
@elseif ($order->status === 'pending_review')
<form method="post" action="{{ route('admin.payment-orders.review', $order) }}">@csrf
<button name="decision" value="approve">Approve after independently verifying receipt</button>
<button name="decision" value="reject">Reject</button></form>
@endif</article>
@empty<p>No payment requests.</p>@endforelse
<h2>Resale copyright review</h2>
@forelse ($resales as $resale)
<article class="member-panel-card mb-3"><strong>{{ $resale->title }}</strong><p>Seller: {{ $resale->seller_name }} · PHP {{ number_format($resale->price_minor / 100, 2) }} · {{ $resale->status }}</p>
<p><a href="{{ route('admin.resales.evidence', $resale->id) }}">View private ownership evidence</a> · {{ $resale->ownership_declaration }}</p>
@if ($resale->status === 'pending_review')<form method="post" action="{{ route('admin.resales.review', $resale->id) }}">@csrf<button name="decision" value="approve">Approve listing</button><button name="decision" value="reject">Reject</button></form>@endif</article>
@empty<p>No resale submissions.</p>@endforelse
<a href="/week12/admin">Back to admin</a></main></body></html>
