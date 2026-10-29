<section class="container py-4 py-lg-5">
    <?php $previewPath = ['home' => '/', 'about' => '/about', 'marketplace' => '/week12'][$slug]; ?>
    <a class="w12-back-link" href="<?= week12_escape(week12_url('/week12/admin')) ?>"><i class="bi bi-arrow-left"></i> Back to admin overview</a>
    <div class="mt-3 mb-4">
        <p class="w12-eyebrow mb-2">ADMIN / WEBSITE</p>
        <h1 class="w12-section-title">Edit website content</h1>
        <p class="text-secondary mb-0">Update public page copy and artwork. Changes are saved to the database.</p>
    </div>

    <?php if (session('status')): ?>
        <div class="alert alert-success" role="status"><?= week12_escape(session('status')) ?></div>
    <?php endif; ?>
    <?php if ($errors->any()): ?>
        <div class="alert alert-danger" role="alert">
            <p class="fw-semibold mb-1">Some content needs attention:</p>
            <ul class="mb-0"><?php foreach ($errors->all() as $error): ?><li><?= week12_escape($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <nav class="nav nav-pills gap-2 mb-4" aria-label="Website pages">
        <?php foreach ($pageSlugs as $pageSlug): ?>
            <?php $pageDefinition = \App\Support\SitePageContent::definitions()[$pageSlug]; ?>
            <a class="nav-link <?= $pageSlug === $slug ? 'active' : '' ?>" href="<?= week12_escape(week12_url('/admin/content/' . $pageSlug)) ?>">
                <?= week12_escape($pageDefinition['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <form method="post" action="<?= week12_escape(week12_url('/admin/content/' . $slug)) ?>" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= week12_escape($week12CsrfToken) ?>">
        <?php foreach ($definition['groups'] as $groupIndex => $group): ?>
            <details class="w12-panel mb-3" <?= $groupIndex === 0 ? 'open' : '' ?>>
                <summary class="h5 mb-0"><?= week12_escape($group['label']) ?></summary>
                <div class="row g-3 mt-1">
                    <?php foreach ($group['fields'] as $field): ?>
                        <?php
                        $parts = explode('.', $field['key']);
                        $inputName = 'content[' . implode('][', $parts) . ']';
                        $fileName = 'images[' . implode('][', $parts) . ']';
                        $value = data_get($content, $field['key'], '');
                        $fieldId = 'field-' . str_replace('.', '-', $field['key']);
                        ?>
                        <div class="<?= in_array($field['type'], ['textarea', 'image'], true) ? 'col-12' : 'col-md-6' ?>">
                            <?php if ($field['type'] === 'image'): ?>
                                <label class="form-label" for="<?= week12_escape($fieldId) ?>"><?= week12_escape($field['label']) ?></label>
                                <?php if (is_string($value) && $value !== ''): ?>
                                    <div class="mb-2"><img src="<?= week12_escape(week12_asset($value)) ?>" alt="" style="max-width: 240px; max-height: 180px; object-fit: cover; border-radius: 0.75rem"></div>
                                <?php endif; ?>
                                <input class="form-control" id="<?= week12_escape($fieldId) ?>" name="<?= week12_escape($fileName) ?>" type="file" accept=".jpg,.jpeg,.png,.gif,.webp">
                                <div class="form-text">Optional. Upload a JPG, PNG, GIF, or WebP image up to 20 MB. Leave empty to keep the current image.</div>
                            <?php else: ?>
                                <label class="form-label" for="<?= week12_escape($fieldId) ?>"><?= week12_escape($field['label']) ?></label>
                                <?php if ($field['type'] === 'textarea'): ?>
                                    <textarea class="form-control" id="<?= week12_escape($fieldId) ?>" name="<?= week12_escape($inputName) ?>" maxlength="<?= (int) $field['max'] ?>" rows="3"><?= week12_escape($value) ?></textarea>
                                <?php else: ?>
                                    <input class="form-control" id="<?= week12_escape($fieldId) ?>" name="<?= week12_escape($inputName) ?>" type="<?= week12_escape($field['type']) ?>" maxlength="<?= (int) $field['max'] ?>" value="<?= week12_escape($value) ?>">
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endforeach; ?>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <button class="btn w12-btn-primary" type="submit"><i class="bi bi-save"></i> Save changes</button>
            <a class="btn w12-btn-outline" href="<?= week12_escape(week12_url($previewPath)) ?>" target="_blank" rel="noopener">Preview public page</a>
        </div>
    </form>
</section>
