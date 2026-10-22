<section class="w12-hero">
    <div class="container py-5 py-lg-6">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <p class="w12-eyebrow"><i class="bi bi-stars"></i> PIXMOREE MARKETPLACE</p>
                <h1>Find your next <span>favorite</span> world.</h1>
                <p class="w12-hero-copy">Browse imaginative digital art from independent creators.</p>
                <a class="btn w12-btn-primary mt-2" href="#artwork">Explore the gallery <i class="bi bi-arrow-down-right"></i></a>
                <p class="w12-simulated-note mt-3 mb-0"><i class="bi bi-info-circle"></i> Fictional classroom data is stored in the local MySQL database.</p>
            </div>
            <div class="col-lg-5">
                <div class="w12-hero-art">
                    <img src="<?= week12_escape(week12_asset('assets/guest-hero-background.png')) ?>" alt="Colorful Pixmoree digital art collage">
                    <span class="w12-art-overlay"><i class="bi bi-lightning-charge-fill"></i> Featured collection</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container py-5" id="artwork">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-3 mb-4">
        <div>
            <p class="w12-eyebrow mb-2">DISCOVER</p>
            <h2 class="w12-section-title">Featured artwork</h2>
            <p class="text-secondary mb-0"><?= count($artworks) ?> listings loaded from the database.</p>
        </div>
        <button class="btn w12-btn-outline" type="button" data-demo-action="Filters are a visual demo; no marketplace query was changed.">
            <i class="bi bi-sliders"></i> Filters (demo)
        </button>
    </div>
    <?php if ($artworks === []): ?>
        <div class="w12-panel text-center py-5">
            <h3 class="h5">No artwork has been added yet</h3>
            <p class="text-secondary mb-0">Add a fictional listing from the admin artwork screen.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($artworks as $artwork): ?>
                <div class="col-sm-6 col-lg-4">
                    <article class="card w12-art-card h-100">
                        <a href="<?= week12_escape(week12_url('/week12/artworks/' . rawurlencode($artwork['id']))) ?>" class="w12-card-image">
                            <img src="<?= week12_escape(week12_asset('assets/' . $artwork['image'])) ?>" alt="<?= week12_escape($artwork['title']) ?>">
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
                                <span class="w12-price"><?= week12_escape($artwork['price']) ?> ETH <small>sample</small></span>
                                <a class="btn w12-btn-outline btn-sm" href="<?= week12_escape(week12_url('/week12/artworks/' . rawurlencode($artwork['id']))) ?>">View details</a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
