<section class="container py-4 py-lg-5">
    <a class="w12-back-link" href="<?= week12_escape(week12_url('/week12/admin')) ?>"><i class="bi bi-arrow-left"></i> Back to admin overview</a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mt-3 mb-4">
        <div>
            <p class="w12-eyebrow mb-2">ADMIN / ACCOUNTS</p>
            <h1 class="w12-section-title">User accounts</h1>
            <p class="text-secondary mb-0">Review registration, email verification, creator activity, and administrator access.</p>
        </div>
        <span class="w12-count"><?= number_format($users->total()) ?> accounts</span>
    </div>
    <?php if (session('status')): ?>
        <div class="alert alert-success" role="status"><?= week12_escape(session('status')) ?></div>
    <?php endif; ?>
    <?php if (isset($errors) && $errors->any()): ?>
        <div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach ($errors->all() as $error): ?><li><?= week12_escape($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <section class="w12-panel">
        <form method="get" action="<?= week12_escape(route('admin.users')) ?>" class="d-flex flex-column flex-sm-row gap-2 mb-3">
            <label class="visually-hidden" for="account-search">Search accounts</label>
            <input class="form-control" id="account-search" name="search" value="<?= week12_escape($search) ?>" placeholder="Search name, username, or email">
            <button class="btn w12-btn-primary" type="submit"><i class="bi bi-search"></i> Search</button>
            <?php if ($search !== ''): ?><a class="btn w12-btn-outline" href="<?= week12_escape(route('admin.users')) ?>">Clear</a><?php endif; ?>
        </form>
        <div class="table-responsive">
            <table class="table w12-table align-middle mb-0">
                <thead><tr><th>Account</th><th>Email</th><th>Verification</th><th>Artwork</th><th>Joined</th><th>Role</th><th>Manage</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $account): ?>
                        <tr>
                            <td><strong><?= week12_escape($account->name) ?></strong><br><small class="text-secondary">@<?= week12_escape($account->username ?: '—') ?></small></td>
                            <td><?= week12_escape($account->email) ?></td>
                            <td><span class="w12-status <?= $account->hasVerifiedEmail() ? '' : 'w12-status-review' ?>"><?= $account->hasVerifiedEmail() ? 'Verified' : 'Unverified' ?></span></td>
                            <td><?= number_format($account->artworks_count) ?></td>
                            <td><?= week12_escape($account->created_at?->format('Y-m-d') ?? '—') ?></td>
                            <td><?= $account->isAdmin() ? 'Administrator' : 'User' ?></td>
                            <td><a class="btn w12-btn-outline btn-sm" href="<?= week12_escape(route('admin.users.edit', $account)) ?>">Edit account</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($users->isEmpty()): ?>
                        <tr><td colspan="7" class="text-secondary">No accounts match this search.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3"><?= $users->links() ?></div>
    </section>
</section>
