<section class="container py-4 py-lg-5">
    <?php $formError = $error ?? (isset($errors) ? $errors->first('listing') : null); ?>
    <a class="w12-back-link" href="<?= week12_escape(week12_url('/week12/admin')) ?>"><i class="bi bi-arrow-left"></i> Back to admin overview</a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mt-3 mb-4">
        <div>
            <p class="w12-eyebrow mb-2">ADMIN / CONTENT</p>
            <h1 class="w12-section-title">Artwork management</h1>
            <p class="text-secondary mb-0">Create, review, and remove fictional listings stored in MySQL.</p>
        </div>
        <a class="btn w12-btn-primary" href="#new-listing"><i class="bi bi-plus-lg"></i> Add listing</a>
    </div>
    <?php if (!empty($formError)): ?>
        <div class="alert alert-danger" role="alert"><?= week12_escape($formError) ?></div>
    <?php endif; ?>
    <div class="alert w12-alert" role="note">
        <i class="bi bi-info-circle-fill"></i>
        <span>This is a classroom demo. Listing changes persist in the local database; do not enter real personal or payment information.</span>
    </div>
    <section class="w12-panel mb-4" id="new-listing">
        <h2 class="h5 mb-3">Add a fictional artwork listing</h2>
        <form method="post" action="<?= week12_escape(week12_url('/week12/admin/artworks')) ?>" class="row g-3">
            <input type="hidden" name="_token" value="<?= week12_escape($week12CsrfToken) ?>">
            <div class="col-md-6">
                <label class="form-label" for="title">Artwork title</label>
                <input class="form-control" id="title" name="title" maxlength="120" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="creator">Creator name</label>
                <input class="form-control" id="creator" name="creator" maxlength="80" required>
            </div>
            <div class="col-sm-6 col-lg-3">
                <label class="form-label" for="category">Category</label>
                <input class="form-control" id="category" name="category" maxlength="50" required>
            </div>
            <div class="col-sm-6 col-lg-3">
                <label class="form-label" for="price">Sample price (ETH)</label>
                <input class="form-control" id="price" name="price" type="number" min="0" max="99999999" step="0.01" required>
            </div>
            <div class="col-sm-6 col-lg-6">
                <label class="form-label" for="image">Project sample image</label>
                <select class="form-select" id="image" name="image" required>
                    <option value="anime-style-character-space.jpg">Neon character art</option>
                    <option value="cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png">Astronaut samurai art</option>
                    <option value="anime-style-character-space (2).jpg">Starlight art</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" maxlength="500" required></textarea>
            </div>
            <div class="col-12"><button class="btn w12-btn-primary" type="submit"><i class="bi bi-database-add"></i> Save to database</button></div>
        </form>
    </section>
    <section class="w12-panel">
        <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mb-3">
            <h2 class="h5 mb-0">Stored artwork listings <span class="w12-count"><?= count($artworks) ?></span></h2>
        </div>
        <div class="table-responsive">
            <table class="table w12-table align-middle mb-0">
                <thead><tr><th>Artwork</th><th>Creator</th><th>Category</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($artworks as $artwork): ?>
                        <tr>
                            <td><div class="d-flex align-items-center gap-2"><img class="w12-table-thumb" src="<?= week12_escape(week12_asset('assets/' . $artwork['image'])) ?>" alt=""><span class="fw-semibold"><?= week12_escape($artwork['title']) ?></span></div></td>
                            <td><?= week12_escape($artwork['creator']) ?></td>
                            <td><?= week12_escape($artwork['category']) ?></td>
                            <td><?= week12_escape($artwork['price']) ?> ETH</td>
                            <td><span class="w12-status <?= $artwork['status'] === 'Review' ? 'w12-status-review' : '' ?>"><?= week12_escape($artwork['status']) ?></span></td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn w12-btn-outline btn-sm" href="<?= week12_escape(week12_url('/week12/artworks/' . rawurlencode($artwork['id']))) ?>">Preview</a>
                                    <form method="post" action="<?= week12_escape(week12_url('/week12/admin/artworks/' . rawurlencode($artwork['id']) . '/status')) ?>" class="d-flex gap-1">
                                        <input type="hidden" name="_token" value="<?= week12_escape($week12CsrfToken) ?>">
                                        <input type="hidden" name="status" value="<?= $artwork['status'] === 'Listed' ? 'Review' : 'Listed' ?>">
                                        <button class="btn w12-btn-outline btn-sm" type="submit"><?= $artwork['status'] === 'Listed' ? 'Unlist' : 'Approve' ?></button>
                                    </form>
                                    <form method="post" action="<?= week12_escape(week12_url('/week12/admin/artworks/' . rawurlencode($artwork['id']) . '/delete')) ?>" onsubmit="return confirm('Remove this fictional listing from the local database?');">
                                        <input type="hidden" name="_token" value="<?= week12_escape($week12CsrfToken) ?>">
                                        <button class="btn w12-btn-danger btn-sm" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($artworks === []): ?>
                        <tr><td colspan="6" class="text-secondary">No artwork is stored yet. Use the form above to add a demo listing.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
