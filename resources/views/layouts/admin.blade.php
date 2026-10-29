<?php
$week12Title ??= 'Pixmoree Admin';
$week12ContentView ??= '';
$week12CsrfToken ??= csrf_token();
$adminUser = auth()->user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Manage Pixmoree website content and artwork.">
    <title><?= week12_escape($week12Title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pixelify+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="<?= week12_escape(week12_asset('css/site.css')) ?>" rel="stylesheet">
    <link href="<?= week12_escape(week12_asset('css/week12.css')) ?>" rel="stylesheet">
    <link href="<?= week12_escape(week12_asset('css/admin-content.css')) ?>" rel="stylesheet">
</head>
<body class="cms-admin-page">
<header class="cms-topbar">
    <div class="cms-topbar-inner">
        <a class="cms-brand" href="<?= week12_escape(week12_url('/')) ?>" aria-label="Pixmoree home">
            <img src="<?= week12_escape(week12_asset('assets/Pixmoree Neon Pixel Logo.png')) ?>" alt="Pixmoree">
            <span>ADMIN STUDIO</span>
        </a>
        <nav class="cms-topnav" aria-label="Admin navigation">
            <a href="<?= week12_escape(week12_url('/week12/admin')) ?>" class="<?= request()->is('week12/admin') ? 'active' : '' ?>">Overview</a>
            <a href="<?= week12_escape(week12_url('/week12/admin/artworks')) ?>" class="<?= request()->is('week12/admin/artworks*') ? 'active' : '' ?>">Artwork</a>
            <a href="<?= week12_escape(week12_url('/admin/content/home')) ?>" class="<?= request()->is('admin/content*') ? 'active' : '' ?>">Website content</a>
            <a href="<?= week12_escape(week12_url('/week12')) ?>">Marketplace</a>
        </nav>
        <div class="cms-account">
            <span class="cms-account-avatar" aria-hidden="true"><?= week12_escape(mb_strtoupper(mb_substr($adminUser->name, 0, 1))) ?></span>
            <span class="cms-account-name"><?= week12_escape($adminUser->name) ?><small>Administrator</small></span>
            <form method="post" action="<?= week12_escape(week12_url('/logout')) ?>">
                <input type="hidden" name="_token" value="<?= week12_escape($week12CsrfToken) ?>">
                <button class="cms-logout" type="submit" aria-label="Log out"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
    </div>
</header>
<main class="cms-admin-main">
    <?php require $week12ContentView; ?>
</main>
<footer class="cms-footer">
    <span>Pixmoree Admin Studio</span>
    <a href="<?= week12_escape(week12_url('/')) ?>">Return to the website <i class="bi bi-arrow-up-right"></i></a>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
