<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit artwork — Pixmoree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
</head>
<body class="profile-settings-page">
<header class="profile-settings-topbar">
    <div class="profile-settings-topbar-inner">
        <a class="profile-settings-brand" href="{{ route('user') }}" aria-label="Pixmoree account home">
            <img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree">
        </a>
        <nav class="profile-settings-nav" aria-label="Account navigation">
            <a class="profile-settings-back-link" href="{{ route('user.artworks.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to listings</a>
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
            <p class="profile-settings-eyebrow"><i class="bi bi-pencil-square" aria-hidden="true"></i> CREATOR STUDIO</p>
            <h1>Edit artwork</h1>
            <p>Update the listing details saved in your account.</p>
        </div>
    </header>

    @if ($errors->any())
        <div class="profile-settings-notice profile-settings-notice-error" role="alert">
            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="profile-settings-grid">
        <section class="profile-settings-panel" aria-labelledby="edit-artwork-heading">
            <div class="profile-settings-panel-heading">
                <span class="profile-settings-panel-icon"><i class="bi bi-image" aria-hidden="true"></i></span>
                <div><h2 id="edit-artwork-heading">{{ $artwork->title }}</h2><p>Changes are validated before they are saved.</p></div>
            </div>
            <form class="profile-settings-form" method="post" action="{{ route('user.artworks.update', ['id' => $artwork->id]) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="profile-settings-field">
                    <label for="artwork-title">Artwork title</label>
                    <input id="artwork-title" name="title" value="{{ old('title', $artwork->title) }}" maxlength="120" required>
                </div>
                <div class="profile-settings-field">
                    <label for="artwork-description">Description</label>
                    <textarea id="artwork-description" name="description" rows="4" maxlength="500" required>{{ old('description', $artwork->description) }}</textarea>
                </div>
                <div class="profile-settings-field-grid">
                    <div class="profile-settings-field">
                        <label for="artwork-category">Category</label>
                        <input id="artwork-category" name="category" value="{{ old('category', $artwork->category) }}" maxlength="50" required>
                    </div>
                    <div class="profile-settings-field">
                        <label for="artwork-price">Price (ETH)</label>
                        <input id="artwork-price" name="price" type="number" min="0" max="99999999" step="0.01" value="{{ old('price', $artwork->price) }}" required>
                    </div>
                </div>
                <div class="profile-settings-field">
                    <label for="artwork-item-count">Items in this edition</label>
                    <input id="artwork-item-count" name="item_count" type="number" min="1" max="100000" step="1" value="{{ old('item_count', $artwork->item_count ?? 1) }}" required>
                </div>
                <div class="profile-settings-field">
                    <label for="artwork-image">Replace image <span>Optional</span></label>
                    <input id="artwork-image" name="image" type="file" accept="image/png,image/jpeg,image/webp">
                    <small>PNG, JPG, or WEBP up to 2 MB. Leave empty to keep the current image.</small>
                </div>
                <button class="profile-settings-primary-button" type="submit"><i class="bi bi-check-lg" aria-hidden="true"></i> Save changes</button>
            </form>
        </section>

        <aside class="profile-settings-panel" aria-labelledby="delete-artwork-heading">
            <div class="profile-settings-panel-heading">
                <span class="profile-settings-panel-icon"><i class="bi bi-trash" aria-hidden="true"></i></span>
                <div><h2 id="delete-artwork-heading">Remove listing</h2><p>This permanently removes the artwork from the marketplace.</p></div>
            </div>
            <form method="post" action="{{ route('user.artworks.destroy', ['id' => $artwork->id]) }}" onsubmit="return confirm('Are you sure you want to permanently delete this artwork listing?');">
                @csrf
                @method('DELETE')
                <button class="profile-settings-delete-button" type="submit"><i class="bi bi-trash" aria-hidden="true"></i> Delete artwork</button>
            </form>
        </aside>
    </div>
</main>
</body>
</html>
