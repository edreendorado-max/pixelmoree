@extends('layouts.week12')

@section('title', 'Admin overview')

@section('content')
<section class="container py-4 py-lg-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="w12-eyebrow mb-2">PIXMOREE / ADMIN</p>
            <h1 class="w12-section-title">Dashboard overview</h1>
            <p class="text-secondary mb-0">A static overview for the marketplace team.</p>
        </div>
        <a class="btn w12-btn-primary" href="{{ route('week12.admin.artworks') }}"><i class="bi bi-images"></i> Manage artwork</a>
    </div>
    <div class="alert w12-alert" role="note">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span><strong>Admin demo:</strong> all metrics are fictional. Admin actions are simulated and are not saved.</span>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><article class="w12-stat-card"><span>Total artwork</span><strong>128</strong><small><i class="bi bi-image"></i> Sample listings</small></article></div>
        <div class="col-6 col-xl-3"><article class="w12-stat-card"><span>Registered users</span><strong>56</strong><small><i class="bi bi-people"></i> Fictional accounts</small></article></div>
        <div class="col-6 col-xl-3"><article class="w12-stat-card"><span>Pending review</span><strong>12</strong><small><i class="bi bi-hourglass-split"></i> Sample queue</small></article></div>
        <div class="col-6 col-xl-3"><article class="w12-stat-card"><span>Simulated sales</span><strong>32</strong><small><i class="bi bi-receipt"></i> No payments</small></article></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <section class="w12-panel h-100">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <div><h2 class="h5 mb-1">Recent listings</h2><p class="text-secondary small mb-0">Illustrative sample records</p></div>
                    <a href="{{ route('week12.admin.artworks') }}" class="w12-text-link">View all <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table w12-table align-middle mb-0">
                        <thead><tr><th>Artwork</th><th>Creator</th><th>Price</th><th>Status</th></tr></thead>
                        <tbody>
                            <tr><td>Neon Wanderer</td><td>pixel.dreams</td><td>0.86 ETH</td><td><span class="w12-status">Listed</span></td></tr>
                            <tr><td>Moonlit Guardian</td><td>neko.studio</td><td>1.24 ETH</td><td><span class="w12-status">Listed</span></td></tr>
                            <tr><td>Starlight Drift</td><td>cosmic.art</td><td>1.52 ETH</td><td><span class="w12-status w12-status-review">Review</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <div class="col-lg-4">
            <section class="w12-panel h-100">
                <p class="w12-eyebrow">WORKSPACE</p>
                <h2 class="h5">Quick actions</h2>
                <p class="text-secondary small">Buttons below demonstrate the interface only.</p>
                <div class="d-grid gap-2">
                    <a href="{{ route('week12.admin.artworks') }}" class="btn w12-btn-outline text-start"><i class="bi bi-card-list"></i> Review artwork list</a>
                    <button type="button" class="btn w12-btn-outline text-start" data-demo-action="Report export is simulated; no file was generated."><i class="bi bi-download"></i> Export report (demo)</button>
                    <button type="button" class="btn w12-btn-outline text-start" data-demo-action="Notification settings are a static demonstration."><i class="bi bi-gear"></i> Settings (demo)</button>
                </div>
            </section>
        </div>
    </div>
</section>
@endsection
