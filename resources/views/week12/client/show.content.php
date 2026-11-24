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
            <?php if ($artwork['status'] === 'Listed' && config('payments.enabled') && (int) ($artwork['user_id'] ?? 0) !== (int) auth()->id()): ?>
                <a class="btn w12-btn-primary w12-detail-buy mt-3" href="<?= week12_escape(route('user.checkout', ['artworkId' => $artwork['id']])) ?>">
                    <i class="bi bi-bag-check" aria-hidden="true"></i> Buy NFT
                </a>
            <?php endif; ?>
            <?php if (config('payments.enabled') && (int) $artwork['owned_item_count'] > 0): ?>
                <details class="w12-resale-disclosure mt-3" <?= $errors->has('price_php') || $errors->has('quantity') || $errors->has('evidence') || $errors->has('ownership_declaration') ? 'open' : '' ?>>
                    <summary class="btn w12-btn-primary w12-detail-buy">
                        <i class="bi bi-arrow-left-right" aria-hidden="true"></i> Resell owned item
                    </summary>
                    <form class="w12-resale-form" method="post" action="<?= week12_escape(route('user.artworks.resale', ['artwork' => $artwork['id']])) ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <label for="resalePrice">Resale price per item (PHP)
                            <input id="resalePrice" name="price_php" type="number" min="1" max="10000000" value="<?= week12_escape(old('price_php', '')) ?>" required>
                            <?php if ($errors->has('price_php')): ?><small class="w12-resale-error" role="alert"><?= week12_escape($errors->first('price_php')) ?></small><?php endif; ?>
                        </label>
                        <label for="resaleQuantity">Number of items to resell
                            <input id="resaleQuantity" name="quantity" type="number" min="1" max="<?= (int) $artwork['owned_item_count'] ?>" value="<?= week12_escape(old('quantity', (int) $artwork['owned_item_count'])) ?>" required>
                            <small>You can list up to <?= number_format((int) $artwork['owned_item_count']) ?> purchased item(s).</small>
                            <?php if ($errors->has('quantity')): ?><small class="w12-resale-error" role="alert"><?= week12_escape($errors->first('quantity')) ?></small><?php endif; ?>
                        </label>
                        <label class="w12-resale-evidence" for="resaleEvidence">Ownership/authorization evidence
                            <input id="resaleEvidence" name="evidence" type="file" accept="image/png,image/jpeg,application/pdf" required>
                            <?php if ($errors->has('evidence')): ?><small class="w12-resale-error" role="alert"><?= week12_escape($errors->first('evidence')) ?></small><?php endif; ?>
                        </label>
                        <label class="w12-resale-declaration"><input name="ownership_declaration" type="checkbox" value="1" <?= old('ownership_declaration') ? 'checked' : '' ?> required> I own or am authorized to resell this artwork and the submission is accurate.</label>
                        <?php if ($errors->has('ownership_declaration')): ?><p class="w12-resale-error" role="alert"><?= week12_escape($errors->first('ownership_declaration')) ?></p><?php endif; ?>
                        <button class="btn w12-btn-primary w12-resale-submit" type="submit">Submit for review</button>
                    </form>
                </details>
            <?php endif; ?>
            <?php if (session()->has('status')): ?>
                <p class="w12-resale-status mt-3" role="status"><?= week12_escape(session('status')) ?></p>
            <?php endif; ?>
            <div class="w12-engagement-panel" data-engagement-metrics="artwork" data-entity-id="<?= week12_escape($artwork['id']) ?>" data-views="<?= (int) $artwork['views_count'] ?>" data-likes="<?= (int) $artwork['likes_count'] ?>">
                <span><i class="bi bi-eye" aria-label="Views"></i> <span data-engagement-views><?= number_format((int) $artwork['views_count']) ?></span> views</span>
                <?php if (auth()->check() && (int) auth()->id() !== (int) ($artwork['user_id'] ?? 0)): ?>
                    <button type="button" data-engagement-like="artwork" data-entity-id="<?= week12_escape($artwork['id']) ?>" data-liked="<?= $artwork['liked'] ? 'true' : 'false' ?>" aria-pressed="<?= $artwork['liked'] ? 'true' : 'false' ?>"><i class="bi <?= $artwork['liked'] ? 'bi-heart-fill' : 'bi-heart' ?>" aria-hidden="true"></i> <span data-engagement-likes><?= number_format((int) $artwork['likes_count']) ?></span> like</button>
                <?php elseif (auth()->check()): ?>
                    <span><i class="bi bi-heart" aria-label="Likes"></i> <span data-engagement-likes><?= number_format((int) $artwork['likes_count']) ?></span> likes</span>
                <?php else: ?>
                    <a href="/login"><i class="bi bi-heart" aria-hidden="true"></i> <span data-engagement-likes><?= number_format((int) $artwork['likes_count']) ?></span> likes · log in to like</a>
                <?php endif; ?>
                <p class="w12-engagement-feedback" data-engagement-feedback role="status" aria-live="polite" hidden></p>
            </div>
            <p class="w12-info-note mt-3" role="status"><i class="bi bi-info-circle"></i> Purchases require a signed-in account. Orders record ownership of a unique edition item here; no payment or blockchain asset is transferred.</p>
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
