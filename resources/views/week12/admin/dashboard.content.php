<section class="container py-4 py-lg-5">
    <?php
    $pendingPayments = (int) ($paymentCounts['pending_review'] ?? 0);
    $completedPayments = (int) ($paymentCounts['approved'] ?? 0) + (int) ($paymentCounts['paid'] ?? 0);
    ?>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="w12-eyebrow mb-2">PIXMOREE / ADMIN</p>
            <h1 class="w12-section-title">Dashboard overview</h1>
            <p class="text-secondary mb-0">Monitor accounts, artwork, payments, and website content.</p>
        </div>
        <a class="btn w12-btn-primary" href="<?= week12_escape(week12_url('/week12/admin/artworks')) ?>"><i class="bi bi-images"></i> Review artwork</a>
    </div>
    <div class="alert w12-alert" role="note">
        <i class="bi bi-info-circle-fill"></i>
        <span><strong>Beta status:</strong> Payments remain subject to the configured feature gate. Review submitted requests only when enabled.</span>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><article class="w12-stat-card"><span>Accounts</span><strong><?= number_format($userCount) ?></strong><small><i class="bi bi-people"></i> <?= number_format($unverifiedUserCount) ?> email unverified</small></article></div>
        <div class="col-6 col-xl-3"><article class="w12-stat-card"><span>Artwork listings</span><strong><?= number_format($counts['total']) ?></strong><small><i class="bi bi-image"></i> <?= number_format($counts['listed']) ?> listed · <?= number_format($counts['review']) ?> in review</small></article></div>
        <div class="col-6 col-xl-3"><article class="w12-stat-card"><span>Payments to review</span><strong><?= number_format($pendingPayments) ?></strong><small><i class="bi bi-receipt"></i> <?= number_format($completedPayments) ?> approved or paid</small></article></div>
        <div class="col-6 col-xl-3"><article class="w12-stat-card"><span>Resales to review</span><strong><?= number_format($pendingResaleCount) ?></strong><small><i class="bi bi-shield-check"></i> Ownership evidence pending</small></article></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <section class="w12-panel h-100">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <div><h2 class="h5 mb-1">Recent artwork</h2><p class="text-secondary small mb-0">Creator listings and asking prices</p></div>
                    <a href="<?= week12_escape(week12_url('/week12/admin/artworks')) ?>" class="w12-text-link">Manage listings <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table w12-table align-middle mb-0">
                        <thead><tr><th>Artwork</th><th>Creator</th><th>Asking price</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($artworks as $artwork): ?>
                            <tr>
                                <td><div class="d-flex align-items-center gap-2"><img class="w12-table-thumb" src="<?= week12_escape(week12_artwork_image($artwork['image'])) ?>" alt=""><span><?= week12_escape($artwork['title']) ?></span></div></td>
                                <td><?= week12_escape($artwork['creator']) ?></td>
                                <td><?= number_format((float) $artwork['price'], 2) ?> ETH</td>
                                <td><span class="w12-status <?= $artwork['status'] === 'Review' ? 'w12-status-review' : '' ?>"><?= week12_escape($artwork['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($artworks === []): ?>
                            <tr><td colspan="4" class="text-secondary">No artwork listings are in the database.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <div class="col-lg-4">
            <section class="w12-panel h-100">
                <p class="w12-eyebrow">ADMIN TOOLS</p>
                <h2 class="h5">Manage Pixmoree</h2>
                <p class="text-secondary small">Moderate creator listings, inspect accounts and review submitted requests.</p>
                <div class="d-grid gap-2">
                    <a href="<?= week12_escape(week12_url('/week12/admin/artworks')) ?>" class="btn w12-btn-outline text-start"><i class="bi bi-card-list"></i> Artwork and prices</a>
                    <a href="<?= week12_escape(week12_url('/admin/users')) ?>" class="btn w12-btn-outline text-start"><i class="bi bi-people"></i> User accounts</a>
                    <a href="<?= week12_escape(week12_url('/admin/analytics')) ?>" class="btn w12-btn-outline text-start"><i class="bi bi-graph-up"></i> Market analytics</a>
                    <a href="<?= week12_escape(week12_url('/admin/payment-orders')) ?>" class="btn w12-btn-outline text-start"><i class="bi bi-receipt"></i> Payments and resales</a>
                    <a href="<?= week12_escape(week12_url('/admin/content/home')) ?>" class="btn w12-btn-outline text-start"><i class="bi bi-pencil-square"></i> Website content</a>
                </div>
            </section>
        </div>
    </div>
</section>
