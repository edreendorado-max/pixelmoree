<?php
$previewPaths = [
    'home' => '/',
    'about' => '/about',
    'marketplace' => '/week12',
    'login' => '/login',
    'register' => '/register',
];
$previewPath = $previewPaths[$slug];
?>
<div class="cms-editor-shell">
    <aside class="cms-sidebar">
        <div class="cms-sidebar-heading">
            <span class="cms-sidebar-icon"><i class="bi bi-window-stack"></i></span>
            <span><strong>Website content</strong><small>Pages and media</small></span>
        </div>
        <p class="cms-sidebar-label">PUBLIC PAGES</p>
        <nav class="cms-page-nav" aria-label="Choose a page to edit">
            <?php foreach ($pageSlugs as $pageSlug): ?>
                <?php $pageDefinition = \App\Support\SitePageContent::definitions()[$pageSlug]; ?>
                <a class="<?= $pageSlug === $slug ? 'active' : '' ?>" href="<?= week12_escape(week12_url('/admin/content/' . $pageSlug)) ?>" <?= $pageSlug === $slug ? 'aria-current="page"' : '' ?>>
                    <i class="bi <?= $pageSlug === 'home' ? 'bi-house' : ($pageSlug === 'about' ? 'bi-info-circle' : ($pageSlug === 'marketplace' ? 'bi-shop' : ($pageSlug === 'login' ? 'bi-box-arrow-in-right' : 'bi-person-plus'))) ?>"></i>
                    <span><?= week12_escape($pageDefinition['label']) ?></span>
                    <?php if ($pageSlug === $slug): ?><i class="bi bi-chevron-right cms-page-current"></i><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <p class="cms-sidebar-label cms-sidebar-label-tools">SITE TOOLS</p>
        <nav class="cms-page-nav" aria-label="Site tools">
            <a href="<?= week12_escape(week12_url('/week12/admin/artworks')) ?>"><i class="bi bi-images"></i><span>Artwork and images</span><i class="bi bi-arrow-up-right cms-external-icon"></i></a>
            <a href="<?= week12_escape(week12_url('/')) ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i><span>Open public website</span></a>
        </nav>
        <div class="cms-sidebar-note">
            <i class="bi bi-cloud-check"></i>
            <div><strong>Changes go live</strong><small>Saved text and images appear on the public page right away.</small></div>
        </div>
    </aside>

    <section class="cms-editor">
        <div class="cms-breadcrumb"><a href="<?= week12_escape(week12_url('/week12/admin')) ?>">Admin overview</a><i class="bi bi-chevron-right"></i><span>Website content</span><i class="bi bi-chevron-right"></i><strong><?= week12_escape($definition['label']) ?></strong></div>
        <header class="cms-editor-heading">
            <div>
                <p class="cms-overline"><i class="bi bi-stars"></i> CONTENT STUDIO</p>
                <h1>Edit <?= week12_escape($definition['label']) ?></h1>
                <p>Update page copy and imagery in one place. Changes are saved to Pixmoree and published immediately.</p>
            </div>
            <a class="cms-preview-button" href="<?= week12_escape(week12_url($previewPath)) ?>" target="_blank" rel="noopener"><i class="bi bi-eye"></i> Preview page</a>
        </header>

        <?php if (session('status')): ?>
            <div class="cms-alert cms-alert-success" role="status"><i class="bi bi-check-circle"></i><?= week12_escape(session('status')) ?></div>
        <?php endif; ?>
        <?php if ($errors->any()): ?>
            <div class="cms-alert cms-alert-error" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                <div><strong>Please review the highlighted content.</strong><ul class="mb-0 mt-1"><?php foreach ($errors->all() as $error): ?><li><?= week12_escape($error) ?></li><?php endforeach; ?></ul></div>
            </div>
        <?php endif; ?>

        <form class="cms-content-form" method="post" action="<?= week12_escape(week12_url('/admin/content/' . $slug)) ?>" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= week12_escape($week12CsrfToken) ?>">
            <?php foreach ($definition['groups'] as $groupIndex => $group): ?>
                <?php $groupIsOpen = $groupIndex === 0 || ($slug === 'about' && $group['label'] === 'People behind Pixmoree'); ?>
                <details class="cms-section" <?= $groupIsOpen ? 'open' : '' ?>>
                    <summary>
                        <span class="cms-section-icon"><i class="bi <?= $groupIndex === 0 ? 'bi-pencil-square' : 'bi-layout-text-window' ?>"></i></span>
                        <span><strong><?= week12_escape($group['label']) ?></strong><small><?= count($group['fields']) ?> editable items</small></span>
                        <i class="bi bi-chevron-down cms-section-chevron"></i>
                    </summary>
                    <div class="cms-fields">
                        <?php foreach ($group['fields'] as $field): ?>
                            <?php
                            $key = $field['key'];
                            $inputName = 'content[' . str_replace('.', '][', $key) . ']';
                            $fileName = 'images[' . str_replace('.', '][', $key) . ']';
                            $value = old('content.' . $key, data_get($content, $key, ''));
                            $fieldId = 'field-' . str_replace('.', '-', $key);
                            ?>
                            <div class="cms-field <?= in_array($field['type'], ['textarea', 'image'], true) ? 'cms-field-wide' : '' ?>">
                                <label for="<?= week12_escape($fieldId) ?>"><?= week12_escape($field['label']) ?></label>
                                <?php if ($field['type'] === 'image'): ?>
                                    <?php if (is_string($value) && $value !== ''): ?>
                                        <div class="cms-image-current">
                                            <img src="<?= week12_escape(week12_asset($value)) ?>" alt="">
                                            <span><i class="bi bi-image"></i> Current image</span>
                                        </div>
                                    <?php endif; ?>
                                    <input class="cms-input cms-file-input" id="<?= week12_escape($fieldId) ?>" name="<?= week12_escape($fileName) ?>" type="file" accept=".jpg,.jpeg,.png,.gif,.webp">
                                    <small class="cms-field-help">Choose a JPG, PNG, GIF, or WebP up to 20 MB. Leave empty to keep the current image.</small>
                                <?php elseif ($field['type'] === 'textarea'): ?>
                                    <textarea class="cms-input" id="<?= week12_escape($fieldId) ?>" name="<?= week12_escape($inputName) ?>" maxlength="<?= (int) $field['max'] ?>" rows="3"><?= week12_escape($value) ?></textarea>
                                <?php else: ?>
                                    <input class="cms-input" id="<?= week12_escape($fieldId) ?>" name="<?= week12_escape($inputName) ?>" type="<?= week12_escape($field['type']) ?>" maxlength="<?= (int) $field['max'] ?>" value="<?= week12_escape($value) ?>">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
            <div class="cms-form-actions">
                <button class="cms-save-button" type="submit"><i class="bi bi-check2-circle"></i> Save and publish</button>
                <a href="<?= week12_escape(week12_url($previewPath)) ?>" target="_blank" rel="noopener">Preview public page <i class="bi bi-arrow-up-right"></i></a>
                <span><i class="bi bi-shield-lock"></i> Admin-only editing</span>
            </div>
        </form>
    </section>
</div>
