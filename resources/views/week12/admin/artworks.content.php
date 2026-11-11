<section class="container py-4 py-lg-5">
    <a class="w12-back-link" href="<?= week12_escape(week12_url('/week12/admin')) ?>"><i class="bi bi-arrow-left"></i> Back to admin overview</a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mt-3 mb-4">
        <div>
            <p class="w12-eyebrow mb-2">ADMIN / MODERATION</p>
            <h1 class="w12-section-title">Artwork management</h1>
            <p class="text-secondary mb-0">Review creator-submitted listings, manage asking prices, and remove policy-violating content.</p>
        </div>
        <span class="w12-count"><?= count($artworks) ?> listings</span>
    </div>
    <?php if (session('status')): ?>
        <div class="alert alert-success" role="status"><?= week12_escape(session('status')) ?></div>
    <?php endif; ?>
    <?php if (isset($errors) && $errors->any()): ?>
        <div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach ($errors->all() as $error): ?><li><?= week12_escape($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <div class="alert w12-alert" role="note">
        <i class="bi bi-info-circle-fill"></i>
        <span>Creators submit artwork from their accounts. Admins review and manage listings; this screen does not create admin-owned artwork.</span>
    </div>
    <section class="w12-panel">
        <div class="table-responsive">
            <table class="table w12-table align-middle mb-0">
                <thead><tr><th>Artwork</th><th>Creator</th><th>Category</th><th>Asking price</th><th>Items</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($artworks as $artwork): ?>
                        <tr>
                            <td><div class="d-flex align-items-center gap-2"><img class="w12-table-thumb" src="<?= week12_escape(week12_artwork_image($artwork['image'])) ?>" alt=""><span class="fw-semibold"><?= week12_escape($artwork['title']) ?></span></div></td>
                            <td><?= week12_escape($artwork['creator']) ?></td>
                            <td><?= week12_escape($artwork['category']) ?></td>
                            <td>
                                <form method="post" action="<?= week12_escape(route('admin.artworks.price', ['id' => $artwork['id']])) ?>" class="d-flex align-items-center gap-1">
                                    <input type="hidden" name="_token" value="<?= week12_escape($week12CsrfToken) ?>">
                                    <label class="visually-hidden" for="price-<?= week12_escape($artwork['id']) ?>">Asking price in ETH for <?= week12_escape($artwork['title']) ?></label>
                                    <input class="form-control form-control-sm" id="price-<?= week12_escape($artwork['id']) ?>" name="price" type="number" min="0" max="99999999" step="0.01" value="<?= week12_escape($artwork['price']) ?>" required style="width:110px">
                                    <span>ETH</span>
                                    <button class="btn w12-btn-outline btn-sm" type="submit">Save</button>
                                </form>
                            </td>
                            <td><?= number_format((int) $artwork['item_count']) ?></td>
                            <td><span class="w12-status <?= $artwork['status'] === 'Review' ? 'w12-status-review' : '' ?>"><?= week12_escape($artwork['status']) ?></span></td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn w12-btn-outline btn-sm" href="<?= week12_escape(week12_url('/week12/artworks/' . rawurlencode($artwork['id']))) ?>">Preview</a>
                                    <form method="post" action="<?= week12_escape(week12_url('/week12/admin/artworks/' . rawurlencode($artwork['id']) . '/status')) ?>" class="d-flex gap-1">
                                        <input type="hidden" name="_token" value="<?= week12_escape($week12CsrfToken) ?>">
                                        <input type="hidden" name="status" value="<?= $artwork['status'] === 'Listed' ? 'Review' : 'Listed' ?>">
                                        <button class="btn w12-btn-outline btn-sm" type="submit"><?= $artwork['status'] === 'Listed' ? 'Unlist' : 'Approve' ?></button>
                                    </form>
                                    <form method="post" action="<?= week12_escape(week12_url('/week12/admin/artworks/' . rawurlencode($artwork['id']) . '/delete')) ?>" onsubmit="return confirm('Remove this artwork from the database?');">
                                        <input type="hidden" name="_token" value="<?= week12_escape($week12CsrfToken) ?>">
                                        <button class="btn w12-btn-danger btn-sm" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($artworks === []): ?>
                        <tr><td colspan="7" class="text-secondary">No creator artwork is stored yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
