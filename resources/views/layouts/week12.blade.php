<?php
$week12Title ??= 'Pixmoree';
$week12Area ??= '';
$week12CsrfToken ??= '';
$week12Description ??= $marketplaceContent['meta_description'] ?? 'Pixmoree marketplace.';
if ($week12Area === 'admin') {
    require week12_view_path('layouts/admin.blade.php');
    return;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= week12_escape(csrf_token()) ?>">
    <meta name="description" content="<?= week12_escape($week12Description) ?>">
    <title><?= week12_escape($week12Title) ?> · Pixmoree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pixelify+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="<?= week12_escape(week12_asset('css/week12.css')) ?>" rel="stylesheet">
    <link href="<?= week12_escape(week12_asset('css/typography.css')) ?>?v=20261111" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark w12-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?= week12_escape(week12_url('/store')) ?>" aria-label="Pixmoree store">
            <img src="<?= week12_escape(week12_asset('assets/Pixmoree Neon Pixel Logo.png')) ?>" alt="Pixmoree">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#week12Nav" aria-controls="week12Nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="week12Nav">
            <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2 py-3 py-lg-0">
                <a class="nav-link <?= $week12Area === 'client' ? 'active' : '' ?>" href="<?= week12_escape(week12_url('/store')) ?>">Store</a>
                <?php if (auth()->check() && auth()->user()->isAdmin()): ?>
                    <a class="nav-link <?= $week12Area === 'admin' ? 'active' : '' ?>" href="<?= week12_escape(week12_url('/week12/admin')) ?>">Admin</a>
                    <a class="nav-link" href="<?= week12_escape(week12_url('/admin/content/about')) ?>">Website content</a>
                <?php endif; ?>
                <span class="w12-beta-pill"><i class="bi bi-info-circle"></i> Beta · database-backed listings</span>
            </div>
        </div>
    </div>
</nav>
<main>
    <?php require $week12ContentView; ?>
</main>
<footer class="w12-footer">
    <div class="container d-flex flex-column flex-sm-row justify-content-between gap-2">
        <span><?= week12_escape($marketplaceContent['footer']['tagline'] ?? 'Pixmoree Marketplace') ?></span>
        <span>Purchases require email verification and manual GCash or Maya receipt review.</span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= week12_escape(week12_asset('js/marketplace.js')) ?>"></script>
<script src="<?= week12_escape(week12_asset('js/engagement.js')) ?>?v=20261104-engagement"></script>
</body>
</html>
