<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My artwork listings — Pixmoree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
    <link href="{{ asset('css/typography.css') }}?v=20261111" rel="stylesheet">
</head>
<body class="profile-settings-page">
<header class="profile-settings-topbar">
    <div class="profile-settings-topbar-inner">
        <a class="profile-settings-brand" href="{{ route('user') }}" aria-label="Pixmoree account home">
            <img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree">
        </a>
        <nav class="profile-settings-nav" aria-label="Account navigation">
            <a href="/user"><i class="bi bi-house" aria-hidden="true"></i> Dashboard</a>
            <a class="profile-settings-back-link" href="/user/create"><i class="bi bi-plus-lg" aria-hidden="true"></i> Create listing</a>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="profile-settings-logout" type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Log out</button>
            </form>
        </nav>
    </div>
</header>

<main class="profile-settings-main">
    <header class="profile-settings-heading">
        <div>
            <p class="profile-settings-eyebrow"><i class="bi bi-images" aria-hidden="true"></i> CREATOR STUDIO</p>
            <h1>My artwork listings</h1>
            <p>View and update artwork saved to your account.</p>
        </div>
        <div class="profile-settings-account-chip">
            <span class="profile-settings-account-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span>
        </div>
    </header>

    @if (session('status'))
        <div class="profile-settings-notice profile-settings-notice-success" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i>{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="profile-settings-notice profile-settings-notice-error" role="alert">
            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="profile-settings-panel" aria-labelledby="artwork-list-heading">
        <div class="profile-settings-panel-heading">
            <span class="profile-settings-panel-icon"><i class="bi bi-collection" aria-hidden="true"></i></span>
            <div><h2 id="artwork-list-heading">Saved listings</h2><p>{{ $artworks->total() }} {{ \Illuminate\Support\Str::plural('artwork', $artworks->total()) }} in your account.</p></div>
        </div>
        @if ($artworks->isEmpty())
            <p class="text-secondary">You have not listed any artwork yet.</p>
            <a class="profile-settings-primary-button" href="/user/create"><i class="bi bi-plus-lg" aria-hidden="true"></i> Create your first listing</a>
        @else
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead><tr><th scope="col">Artwork</th><th scope="col">Category</th><th scope="col">Price</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    @foreach ($artworks as $artwork)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ asset($artwork->image) }}" alt="" width="56" height="56" class="rounded object-fit-cover">
                                    <span><strong>{{ $artwork->title }}</strong><br><small class="text-secondary">{{ $artwork->created_at?->format('M j, Y') }}</small></span>
                                </div>
                            </td>
                            <td>{{ $artwork->category }}</td>
                            <td>Ξ {{ number_format((float) $artwork->price, 2) }}</td>
                            <td>{{ $artwork->status }}</td>
                            <td class="text-end"><a class="profile-settings-secondary-button" href="{{ route('user.artworks.edit', ['id' => $artwork->id]) }}"><i class="bi bi-pencil" aria-hidden="true"></i> Edit</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $artworks->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
</main>
</body>
</html>
