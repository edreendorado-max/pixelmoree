<section class="container py-4 py-lg-5">
    <a class="w12-back-link" href="<?= week12_escape(week12_url('/week12')) ?>"><i class="bi bi-arrow-left"></i> Back to marketplace</a>
    <div class="row g-4 g-lg-5 mt-1 align-items-start">
        <div class="col-lg-6">
            <div class="w12-detail-image">
                <img src="<?= week12_escape(week12_artwork_image($artwork['image'])) ?>" alt="<?= week12_escape($artwork['title']) ?>">
            </div>
        </div>
        <div class="col-lg-6">
            <p class="w12-eyebrow mt-lg-2">DIGITAL ARTWORK</p>
            <span class="w12-category"><?= week12_escape($artwork['category']) ?></span>
            <h1 class="w12-detail-title mt-2"><?= week12_escape($artwork['title']) ?></h1>
            <p class="text-secondary">Created by <strong class="text-light"><?= week12_escape($artwork['creator']) ?></strong></p>
            <p class="w12-detail-copy"><?= week12_escape($artwork['description']) ?></p>
            <div class="w12-price-panel">
                <span>Creator's demo asking price</span>
                <strong><?= week12_escape($artwork['price']) ?> ETH</strong>
            </div>
            <div class="w12-price-panel mt-3">
                <span>Estimated market value (demo only)</span>
                <strong><?= number_format((float) $artwork['estimated_market_value'], 2) ?> ETH</strong>
                <span><?= $artwork['market_change_percent'] >= 0 ? '+' : '' ?><?= number_format((float) $artwork['market_change_percent'], 1) ?>% vs. asking price</span>
            </div>
            <div class="d-grid d-sm-flex gap-2 mt-3">
                <button type="button" class="btn w12-btn-primary" data-demo-action="Purchase is simulated. No payment or transaction was made.">
                    <i class="bi bi-bag"></i> Buy artwork (demo)
                </button>
                <button type="button" class="btn w12-btn-outline" data-demo-action="Favorite is simulated; it is not stored.">
                    <i class="bi bi-heart"></i> Add to favorites (demo)
                </button>
            </div>
            <p class="w12-simulated-note mt-3"><i class="bi bi-shield-check"></i> Artwork and creator accounts are stored in MySQL. Value estimates use demo purchase activity and are not financial valuations. Purchases and blockchain transactions are not enabled.</p>
            <div class="w12-info-panel mt-4">
                <h2 class="h6 mb-3">Listing information</h2>
                <dl class="row mb-0">
                    <dt class="col-5">Creator</dt><dd class="col-7"><?= week12_escape($artwork['creator']) ?></dd>
                    <dt class="col-5">Category</dt><dd class="col-7"><?= week12_escape($artwork['category']) ?></dd>
                    <dt class="col-5">Status</dt><dd class="col-7"><?= week12_escape($artwork['status']) ?></dd>
                    <dt class="col-5">Blockchain</dt><dd class="col-7">Illustrative only</dd>
                </dl>
            </div>
        </div>
    </div>
</section>
