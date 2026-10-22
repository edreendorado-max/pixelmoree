<?php
$week12Title ??= 'Pixmoree';
$week12Area ??= '';
$week12CsrfToken ??= '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Pixmoree Week 12 marketplace database demonstration.">
    <title><?= week12_escape($week12Title) ?> · Pixmoree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pixelify+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="<?= week12_escape(week12_asset('css/week12.css')) ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark w12-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?= week12_escape(week12_url('/week12')) ?>" aria-label="Pixmoree marketplace">
            <img src="<?= week12_escape(week12_asset('assets/Pixmoree Neon Pixel Logo.png')) ?>" alt="Pixmoree">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#week12Nav" aria-controls="week12Nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="week12Nav">
            <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2 py-3 py-lg-0">
                <a class="nav-link <?= $week12Area === 'client' ? 'active' : '' ?>" href="<?= week12_escape(week12_url('/week12')) ?>">Marketplace</a>
                <a class="nav-link <?= $week12Area === 'admin' ? 'active' : '' ?>" href="<?= week12_escape(week12_url('/week12/admin')) ?>">Admin demo</a>
                <span class="w12-demo-pill"><i class="bi bi-info-circle"></i> MySQL-backed listings</span>
            </div>
        </div>
    </div>
</nav>
<main>
    <?php require $week12ContentView; ?>
</main>
<footer class="w12-footer">
    <div class="container d-flex flex-column flex-sm-row justify-content-between gap-2">
        <span>Pixmoree · Week 12 interface prototype</span>
        <span>Fictional demo listings · no real payments or accounts</span>
    </div>
</footer>
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="demoToast" class="toast text-bg-dark border border-secondary" role="status" aria-live="polite" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">Simulated action only.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('[data-demo-action]').forEach((button) => {
        button.addEventListener('click', () => {
            const toast = document.getElementById('demoToast');
            toast.querySelector('.toast-body').textContent = button.dataset.demoAction;
            bootstrap.Toast.getOrCreateInstance(toast).show();
        });
    });
</script>
</body>
</html>
