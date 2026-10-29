<section class="container py-4 py-lg-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="w12-eyebrow mb-2">PIXMOREE / ADMIN</p>
            <h1 class="w12-section-title">Dashboard overview</h1>
            <p class="text-secondary mb-0">Live listing totals from the local MySQL database.</p>
        </div>
        <a class="btn w12-btn-primary" href="<?= week12_escape(week12_url('/week12/admin/artworks')) ?>"><i class="bi bi-images"></i> Manage artwork</a>
    </div>
    <div class="alert w12-alert" role="note">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span><strong>Class demo:</strong> artwork changes are saved locally. All creator, price, and artwork records are fictional.</span>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><article class="w12-stat-card"><span>Total artwork</span><strong><?= week12_escape($counts['total']) ?></strong><small><i class="bi bi-image"></i> Database records</small></article></div>
        <div class="col-6 col-xl-3"><article class="w12-stat-card"><span>Listed</span><strong><?= week12_escape($counts['listed']) ?></strong><small><i class="bi bi-check-circle"></i> Active demo listings</small></article></div>
        <div class="col-6 col-xl-3"><article class="w12-stat-card"><span>Pending review</span><strong><?= week12_escape($counts['review']) ?></strong><small><i class="bi bi-hourglass-split"></i> Awaiting demo review</small></article></div>
        <div class="col-6 col-xl-3"><article class="w12-stat-card"><span>Payments</span><strong>0</strong><small><i class="bi bi-receipt"></i> Not implemented</small></article></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <section class="w12-panel h-100">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <div><h2 class="h5 mb-1">Recent listings</h2><p class="text-secondary small mb-0">Stored in the local database</p></div>
                    <a href="<?= week12_escape(week12_url('/week12/admin/artworks')) ?>" class="w12-text-link">View all <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table w12-table align-middle mb-0">
                        <thead><tr><th>Artwork</th><th>Creator</th><th>Price</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($artworks as $artwork): ?>
                            <tr>
                                <td><?= week12_escape($artwork['title']) ?></td>
                                <td><?= week12_escape($artwork['creator']) ?></td>
                                <td><?= week12_escape($artwork['price']) ?> ETH</td>
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
                <p class="w12-eyebrow">WORKSPACE</p>
                <h2 class="h5">Quick actions</h2>
                <p class="text-secondary small">Artwork-management actions update the local database.</p>
                <div class="d-grid gap-2">
                    <a href="<?= week12_escape(week12_url('/admin/content/about')) ?>" class="btn w12-btn-outline text-start"><i class="bi bi-pencil-square"></i> Edit website content</a>
                    <a href="<?= week12_escape(week12_url('/week12/admin/artworks#new-listing')) ?>" class="btn w12-btn-outline text-start"><i class="bi bi-plus-circle"></i> Add demo listing</a>
                    <a href="<?= week12_escape(week12_url('/week12/admin/artworks')) ?>" class="btn w12-btn-outline text-start"><i class="bi bi-card-list"></i> Review artwork list</a>
                </div>
            </section>
        </div>
    </div>
</section>
