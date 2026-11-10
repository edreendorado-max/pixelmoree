<section class="w12-hero">
    <div class="container py-5 py-lg-6">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <p class="w12-eyebrow"><i class="bi bi-stars"></i> <?= week12_escape($marketplaceContent['hero']['eyebrow']) ?></p>
                <h1><?= week12_escape($marketplaceContent['hero']['heading_before']) ?> <span><?= week12_escape($marketplaceContent['hero']['heading_emphasis']) ?></span> <?= week12_escape($marketplaceContent['hero']['heading_after']) ?></h1>
                <p class="w12-hero-copy"><?= week12_escape($marketplaceContent['hero']['description']) ?></p>
                <a class="btn w12-btn-primary mt-2" href="#artwork"><?= week12_escape($marketplaceContent['hero']['cta']) ?> <i class="bi bi-arrow-down-right"></i></a>
                <p class="w12-beta-note mt-3 mb-0" role="status"><i class="bi bi-info-circle"></i> Purchases, payments, and blockchain minting are disabled during beta.</p>
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
            <p class="text-secondary mb-0"><span id="marketplaceResultCount"><?= count($artworks) ?></span> <?= week12_escape($marketplaceContent['artwork']['count_label']) ?></p>
        </div>
        <form class="w12-market-filters" id="marketplaceFilters">
            <div class="w12-market-filter w12-market-filter-search">
                <label for="marketplaceSearch">Search listings</label>
                <input id="marketplaceSearch" type="search" placeholder="Title or creator">
            </div>
            <div class="w12-market-filter">
                <label for="marketplaceCategory">Category</label>
                <select id="marketplaceCategory">
                    <option value="">All categories</option>
                    <?php foreach (array_unique(array_column($artworks, 'category')) as $category): ?>
                        <option value="<?= week12_escape(mb_strtolower($category)) ?>"><?= week12_escape($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w12-market-filter">
                <label for="marketplaceSort">Sort by</label>
                <select id="marketplaceSort">
                    <option value="newest">Newest</option>
                    <option value="price-low">Price: low to high</option>
                    <option value="price-high">Price: high to low</option>
                    <option value="title">Title</option>
                </select>
            </div>
        </form>
    </div>
    <p id="marketplaceNoResults" class="w12-panel text-center py-4" hidden>No listings match those filters.</p>
    <?php if ($artworks === []): ?>
        <div class="w12-panel text-center py-5">
            <h3 class="h5"><?= week12_escape($marketplaceContent['artwork']['empty_heading']) ?></h3>
            <p class="text-secondary mb-0"><?= week12_escape($marketplaceContent['artwork']['empty_body']) ?></p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($artworks as $artwork): ?>
                <div class="col-sm-6 col-lg-4" data-marketplace-item data-title="<?= week12_escape(mb_strtolower($artwork['title'])) ?>" data-creator="<?= week12_escape(mb_strtolower($artwork['creator'])) ?>" data-category="<?= week12_escape(mb_strtolower($artwork['category'])) ?>" data-price="<?= week12_escape((string) $artwork['price']) ?>" data-created="<?= week12_escape((string) ($artwork['created_at'] ?? '')) ?>">
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
                            <p class="w12-engagement-inline" data-engagement-metrics="artwork" data-entity-id="<?= week12_escape($artwork['id']) ?>" data-views="<?= (int) $artwork['views_count'] ?>" data-likes="<?= (int) $artwork['likes_count'] ?>">
                                <span><i class="bi bi-eye" aria-label="Views"></i> <span data-engagement-views><?= number_format((int) $artwork['views_count']) ?></span></span>
                                <span><i class="bi bi-heart" aria-label="Likes"></i> <span data-engagement-likes><?= number_format((int) $artwork['likes_count']) ?></span></span>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="w12-price"><?= week12_escape($artwork['price']) ?> ETH <small>asking price</small><br><small><?= number_format((int) $artwork['item_count']) ?> items</small></span>
                                <a class="btn w12-btn-outline btn-sm" href="<?= week12_escape(week12_url('/week12/artworks/' . rawurlencode($artwork['id']))) ?>">View details</a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
