@extends('layouts.week12')

@section('title', $artwork['title'])

@section('content')
<section class="container py-4 py-lg-5">
    <a class="w12-back-link" href="{{ route('week12.client') }}"><i class="bi bi-arrow-left"></i> Back to marketplace</a>
    <div class="row g-4 g-lg-5 mt-1 align-items-start">
        <div class="col-lg-6">
            <div class="w12-detail-image">
                <img src="{{ asset($artwork['image']) }}" alt="{{ $artwork['title'] }}">
            </div>
        </div>
        <div class="col-lg-6">
            <p class="w12-eyebrow mt-lg-2">DIGITAL COLLECTIBLE · SAMPLE</p>
            <span class="w12-category">{{ $artwork['category'] }}</span>
            <h1 class="w12-detail-title mt-2">{{ $artwork['title'] }}</h1>
            <p class="text-secondary">Created by <strong class="text-light">{{ $artwork['creator'] }}</strong></p>
            <p class="w12-detail-copy">{{ $artwork['description'] }}</p>
            <div class="w12-price-panel">
                <span>Asking price <small>(fictional)</small></span>
                <strong>{{ $artwork['price'] }} ETH</strong>
            </div>
            <div class="d-grid d-sm-flex gap-2 mt-3">
                <button type="button" class="btn w12-btn-primary" data-demo-action="Purchase is simulated. No payment or transaction was made.">
                    <i class="bi bi-bag"></i> Buy artwork (demo)
                </button>
                <button type="button" class="btn w12-btn-outline" data-demo-action="Favorite saved for demonstration only; it is not stored.">
                    <i class="bi bi-heart"></i> Add to favorites (demo)
                </button>
            </div>
            <p class="w12-simulated-note mt-3"><i class="bi bi-shield-check"></i> Simulated actions only. This prototype has no payment or database integration.</p>
            <div class="w12-info-panel mt-4">
                <h2 class="h6 mb-3">Listing information</h2>
                <dl class="row mb-0">
                    <dt class="col-5">Creator</dt><dd class="col-7">{{ $artwork['creator'] }}</dd>
                    <dt class="col-5">Category</dt><dd class="col-7">{{ $artwork['category'] }}</dd>
                    <dt class="col-5">Status</dt><dd class="col-7">{{ $artwork['status'] }} · sample</dd>
                    <dt class="col-5">Blockchain</dt><dd class="col-7">Illustrative only</dd>
                </dl>
            </div>
        </div>
    </div>
</section>
@endsection
