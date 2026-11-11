<section class="container py-4 py-lg-5">
    <a class="w12-back-link" href="<?= week12_escape(route('admin.users')) ?>"><i class="bi bi-arrow-left"></i> Back to user accounts</a>
    <header class="mt-3 mb-4">
        <p class="w12-eyebrow mb-2">ADMIN / ACCOUNTS</p>
        <h1 class="w12-section-title">Edit account</h1>
        <p class="text-secondary mb-0">Update profile details for <?= week12_escape($account->name) ?>. Passwords and administrator roles are not editable here.</p>
    </header>
    <?php if (session('status')): ?>
        <div class="alert alert-success" role="status"><?= week12_escape(session('status')) ?></div>
    <?php endif; ?>
    <?php if (isset($errors) && $errors->any()): ?>
        <div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach ($errors->all() as $error): ?><li><?= week12_escape($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <section class="w12-panel">
        <form method="post" action="<?= week12_escape(route('admin.users.update', $account)) ?>" class="row g-3">
            <input type="hidden" name="_token" value="<?= week12_escape($week12CsrfToken) ?>">
            <input type="hidden" name="_method" value="PUT">
            <div class="col-md-6">
                <label class="form-label" for="account-name">Display name</label>
                <input class="form-control" id="account-name" name="name" value="<?= week12_escape(old('name', $account->name)) ?>" maxlength="120" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="account-username">Username</label>
                <input class="form-control" id="account-username" name="username" value="<?= week12_escape(old('username', $account->username)) ?>" maxlength="30" pattern="[A-Za-z0-9._-]*">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="account-email">Email address</label>
                <input class="form-control" id="account-email" name="email" type="email" value="<?= week12_escape(old('email', $account->email)) ?>" maxlength="254" required>
                <div class="form-text">Changing the email address resets its verification status.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="account-phone">Phone</label>
                <input class="form-control" id="account-phone" name="phone" type="tel" value="<?= week12_escape(old('phone', $account->phone)) ?>" maxlength="30">
            </div>
            <div class="col-12">
                <label class="form-label" for="account-bio">Bio</label>
                <textarea class="form-control" id="account-bio" name="bio" rows="4" maxlength="2000"><?= week12_escape(old('bio', $account->bio)) ?></textarea>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
                <button class="btn w12-btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Save account</button>
                <a class="btn w12-btn-outline" href="<?= week12_escape(route('admin.users')) ?>">Cancel</a>
            </div>
        </form>
    </section>
</section>
