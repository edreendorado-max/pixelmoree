<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile settings — Pixmoree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
</head>
<body class="auth-page">
<main class="container py-5" style="max-width: 860px">
    <header class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <a href="/" aria-label="Pixmoree home"><img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree" style="max-width: 180px"></a>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-secondary" type="submit">Log out</button>
        </form>
    </header>

    <h1 class="h2 mb-2">Profile settings</h1>
    <p class="text-secondary mb-4">Update the information saved to your Pixmoree account and manage your uploaded images.</p>

    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h4 mb-3">Your information</h2>
            <form method="post" action="{{ route('user.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @if ($user->avatar_path)
                    <div class="mb-3">
                        <img src="{{ asset($user->avatar_path) }}" alt="Current profile image" class="rounded-circle" style="width: 96px; height: 96px; object-fit: cover">
                    </div>
                @endif
                <div class="mb-3">
                    <label class="form-label" for="profile-avatar">Creator profile image</label>
                    <input class="form-control" id="profile-avatar" name="avatar" type="file" accept=".jpg,.jpeg,.png,.gif,.webp">
                    <div class="form-text">Optional. JPG, PNG, GIF or WebP; maximum file size 5 MB.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="profile-name">Display name</label>
                    <input class="form-control" id="profile-name" name="name" value="{{ old('name', $user->name) }}" maxlength="120" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="profile-email">Email address</label>
                    <input class="form-control" id="profile-email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="254" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="profile-phone">Phone (optional)</label>
                    <input class="form-control" id="profile-phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" maxlength="30">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="profile-bio">Bio (optional)</label>
                    <textarea class="form-control" id="profile-bio" name="bio" rows="4" maxlength="2000">{{ old('bio', $user->bio) }}</textarea>
                </div>
                <button class="btn btn-primary" type="submit">Save profile</button>
            </form>
        </div>
    </section>

    <section class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h4 mb-2">Change password</h2>
            <p class="text-secondary">Use a password that is easy for you to remember, at least 8 characters long.</p>
            <form method="post" action="{{ route('user.password.update') }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="current-password">Current password</label>
                    <input class="form-control" id="current-password" name="current_password" type="password" autocomplete="current-password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="new-password">New password</label>
                    <input class="form-control" id="new-password" name="new_password" type="password" minlength="8" maxlength="128" autocomplete="new-password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="new-password-confirmation">Confirm new password</label>
                    <input class="form-control" id="new-password-confirmation" name="new_password_confirmation" type="password" minlength="8" maxlength="128" autocomplete="new-password" required>
                </div>
                <button class="btn btn-primary" type="submit">Update password</button>
            </form>
        </div>
    </section>

    <section class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h4 mb-3">Upload an image</h2>
            <p class="text-secondary">JPG, PNG, GIF or WebP; maximum file size 5 MB.</p>
            <form method="post" action="{{ route('user.images.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="image-file">Image file</label>
                    <input class="form-control" id="image-file" name="image" type="file" accept=".jpg,.jpeg,.png,.gif,.webp" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="image-alt-text">Description (optional)</label>
                    <input class="form-control" id="image-alt-text" name="alt_text" maxlength="255">
                </div>
                <button class="btn btn-primary" type="submit">Upload image</button>
            </form>
        </div>
    </section>

    <section class="card shadow-sm">
        <div class="card-body p-4">
            <h2 class="h4 mb-3">Your images <span class="badge text-bg-secondary">{{ $images->count() }}</span></h2>
            @forelse ($images as $image)
                <article class="border rounded p-3 mb-3">
                    <div class="d-flex flex-column flex-sm-row gap-3 align-items-sm-start">
                        <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $image->alt_text ?? 'Uploaded image' }}" class="rounded object-fit-cover" style="width: 120px; height: 120px">
                        <div class="flex-grow-1">
                            <p class="small text-secondary mb-2">{{ number_format($image->size_bytes / 1024, 1) }} KB · {{ $image->mime_type }}</p>
                            <form method="post" action="{{ route('user.images.update', $image->id) }}" class="d-flex flex-column flex-sm-row gap-2 mb-2">
                                @csrf
                                <label class="visually-hidden" for="alt-text-{{ $image->id }}">Image description</label>
                                <input class="form-control" id="alt-text-{{ $image->id }}" name="alt_text" value="{{ $image->alt_text }}" maxlength="255" placeholder="Image description">
                                <button class="btn btn-outline-primary flex-shrink-0" type="submit">Update description</button>
                            </form>
                            <form method="post" action="{{ route('user.images.destroy', $image->id) }}" onsubmit="return confirm('Delete this image?');">
                                @csrf
                                <button class="btn btn-outline-danger btn-sm" type="submit">Delete image</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-secondary mb-0">You have not uploaded any images yet.</p>
            @endforelse
        </div>
    </section>
</main>
</body>
</html>
