<section class="container py-4 py-lg-5">
    <a class="w12-back-link" href="<?= week12_escape(week12_url('/store')) ?>"><i class="bi bi-arrow-left"></i> Back to store</a>
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
                <span>Creator's asking price</span>
                <strong><?= week12_escape($artwork['price']) ?> ETH</strong>
                <span><?= number_format((int) $artwork['item_count']) ?> items in this edition</span>
            </div>
            <div class="w12-engagement-panel" data-engagement-metrics="artwork" data-entity-id="<?= week12_escape($artwork['id']) ?>" data-views="<?= (int) $artwork['views_count'] ?>" data-likes="<?= (int) $artwork['likes_count'] ?>">
                <span><i class="bi bi-eye" aria-label="Views"></i> <span data-engagement-views><?= number_format((int) $artwork['views_count']) ?></span> views</span>
                <?php if (auth()->check()): ?>
                    <button type="button" data-engagement-like="artwork" data-entity-id="<?= week12_escape($artwork['id']) ?>" data-liked="<?= $artwork['liked'] ? 'true' : 'false' ?>" aria-pressed="<?= $artwork['liked'] ? 'true' : 'false' ?>"><i class="bi <?= $artwork['liked'] ? 'bi-heart-fill' : 'bi-heart' ?>" aria-hidden="true"></i> <span data-engagement-likes><?= number_format((int) $artwork['likes_count']) ?></span> like</button>
                <?php else: ?>
                    <a href="/login"><i class="bi bi-heart" aria-hidden="true"></i> <span data-engagement-likes><?= number_format((int) $artwork['likes_count']) ?></span> likes · log in to like</a>
                <?php endif; ?>
                <p class="w12-engagement-feedback" data-engagement-feedback role="status" aria-live="polite" hidden></p>
            </div>
            <p class="w12-beta-note mt-3" role="status"><i class="bi bi-info-circle"></i> Purchases, payments, and blockchain minting are disabled during beta.</p>
            <div class="w12-info-panel mt-4">
                <h2 class="h6 mb-3">Listing information</h2>
                <dl class="row mb-0">
                    <dt class="col-5">Creator</dt><dd class="col-7"><?= week12_escape($artwork['creator']) ?></dd>
                    <dt class="col-5">Category</dt><dd class="col-7"><?= week12_escape($artwork['category']) ?></dd>
                    <dt class="col-5">Status</dt><dd class="col-7"><?= week12_escape($artwork['status']) ?></dd>
                    <dt class="col-5">Blockchain</dt><dd class="col-7">Not enabled</dd>
                </dl>
            </div>
        </div>
    </div>
</section>
