<section class="w12-hero">
    <div class="container py-5 py-lg-6">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <p class="w12-eyebrow"><i class="bi bi-stars"></i> <?= week12_escape($marketplaceContent['hero']['eyebrow']) ?></p>
                <h1><?= week12_escape($marketplaceContent['hero']['heading_before']) ?> <span><?= week12_escape($marketplaceContent['hero']['heading_emphasis']) ?></span> <?= week12_escape($marketplaceContent['hero']['heading_after']) ?></h1>
                <p class="w12-hero-copy"><?= week12_escape($marketplaceContent['hero']['description']) ?></p>
                <a class="btn w12-btn-primary mt-2" href="#artwork"><?= week12_escape($marketplaceContent['hero']['cta']) ?> <i class="bi bi-arrow-down-right"></i></a>
                <p class="w12-simulated-note mt-3 mb-0"><i class="bi bi-info-circle"></i> <?= week12_escape($marketplaceContent['hero']['note']) ?></p>
            </div>
            <div class="col-lg-5">
                <div class="w12-hero-art">
                    <img src="<?= week12_escape(week12_asset($marketplaceContent['hero']['image'])) ?>" alt="<?= week12_escape($marketplaceContent['hero']['image_alt']) ?>">
                    <span class="w12-art-overlay"><i class="bi bi-lightning-charge-fill"></i> Featured collection</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container py-5" id="artwork">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-3 mb-4">
        <div>
            <p class="w12-eyebrow mb-2"><?= week12_escape($marketplaceContent['artwork']['eyebrow']) ?></p>
            <h2 class="w12-section-title"><?= week12_escape($marketplaceContent['artwork']['heading']) ?></h2>
            <p class="text-secondary mb-0"><?= count($artworks) ?> <?= week12_escape($marketplaceContent['artwork']['count_label']) ?></p>
            <p class="text-secondary small mt-2 mb-0">Estimates use recent demo purchases and listing inactivity. They are informational only; creators' asking prices do not change.</p>
        </div>
        <button class="btn w12-btn-outline" type="button" data-demo-action="Filters are a visual demo; no marketplace query was changed.">
            <i class="bi bi-sliders"></i> <?= week12_escape($marketplaceContent['artwork']['filters_label']) ?>
        </button>
    </div>
    <?php if ($artworks === []): ?>
        <div class="w12-panel text-center py-5">
            <h3 class="h5"><?= week12_escape($marketplaceContent['artwork']['empty_heading']) ?></h3>
            <p class="text-secondary mb-0"><?= week12_escape($marketplaceContent['artwork']['empty_body']) ?></p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($artworks as $artwork): ?>
                <div class="col-sm-6 col-lg-4">
                    <article class="card w12-art-card h-100">
                        <a href="<?= week12_escape(week12_url('/week12/artworks/' . rawurlencode($artwork['id']))) ?>" class="w12-card-image">
                            <img src="<?= week12_escape(week12_artwork_image($artwork['image'])) ?>" alt="<?= week12_escape($artwork['title']) ?>">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <span class="w12-category"><?= week12_escape($artwork['category']) ?></span>
                                    <h3 class="h5 mt-2 mb-1"><?= week12_escape($artwork['title']) ?></h3>
                                </div>
                                <span class="w12-status <?= $artwork['status'] === 'Review' ? 'w12-status-review' : '' ?>"><?= week12_escape($artwork['status']) ?></span>
                            </div>
                            <p class="text-secondary mb-3">By <?= week12_escape($artwork['creator']) ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="w12-price"><?= week12_escape($artwork['price']) ?> ETH <small>asking price</small><br><small>Est. <?= number_format((float) $artwork['estimated_market_value'], 2) ?> ETH (<?= $artwork['market_change_percent'] >= 0 ? '+' : '' ?><?= number_format((float) $artwork['market_change_percent'], 1) ?>%)</small></span>
                                <a class="btn w12-btn-outline btn-sm" href="<?= week12_escape(week12_url('/week12/artworks/' . rawurlencode($artwork['id']))) ?>">View details</a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
