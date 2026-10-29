<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile settings — Pixmoree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
</head>
<body class="auth-page profile-settings-page">
<header class="profile-settings-topbar">
    <div class="profile-settings-topbar-inner">
        <a class="profile-settings-brand" href="/" aria-label="Pixmoree home">
            <img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree">
        </a>
        <nav class="profile-settings-nav" aria-label="Account navigation">
            <a href="/week12"><i class="bi bi-compass" aria-hidden="true"></i> Explore</a>
            <a href="/user"><i class="bi bi-grid" aria-hidden="true"></i> Account</a>
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
            <p class="profile-settings-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> PIXMOREE ACCOUNT</p>
            <h1>Profile settings</h1>
            <p>Manage your creator identity, account security, and profile banner.</p>
        </div>
        <div class="profile-settings-account-chip">
            <span class="profile-settings-account-avatar">
                @if ($user->avatar_path)
                    <img src="{{ asset($user->avatar_path) }}" alt="">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
            </span>
            <span><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></span>
        </div>
    </header>

    @if (session('status'))
        <div class="profile-settings-notice profile-settings-notice-success" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i>{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="profile-settings-notice profile-settings-notice-error" role="alert">
            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="profile-settings-grid">
        <section class="profile-settings-panel profile-settings-profile-panel" aria-labelledby="profile-info-heading">
            <div class="profile-settings-panel-heading">
                <span class="profile-settings-panel-icon"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                <div><h2 id="profile-info-heading">Profile information</h2><p>How you appear to the Pixmoree community.</p></div>
            </div>
            <form class="profile-settings-form" method="post" action="{{ route('user.profile.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="profile-settings-banner-control">
                    <div class="profile-settings-banner-preview">
                        @if ($user->banner_path)
                            <img src="{{ asset($user->banner_path) }}" alt="Current profile banner">
                        @endif
                        <span><i class="bi bi-stars" aria-hidden="true"></i> PROFILE BANNER</span>
                    </div>
                    <div class="profile-settings-field">
                        <label for="profile-banner">Upload a profile banner</label>
                        <input id="profile-banner" name="banner" type="file" accept=".jpg,.jpeg,.png,.gif,.webp">
                        <small>JPG, PNG, GIF, or WebP. Maximum 10 MB. Wide images work best.</small>
                    </div>
                </div>
                <div class="profile-settings-avatar-row">
                    <span class="profile-settings-avatar-preview">
                        @if ($user->avatar_path)
                            <img src="{{ asset($user->avatar_path) }}" alt="Current profile image">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </span>
                    <div class="profile-settings-field profile-settings-avatar-field">
                        <label for="profile-avatar">Creator profile image</label>
                        <input id="profile-avatar" name="avatar" type="file" accept=".jpg,.jpeg,.png,.gif,.webp">
                        <small>JPG, PNG, GIF, or WebP. Maximum 5 MB.</small>
                    </div>
                </div>
                <div class="profile-settings-field-grid">
                    <div class="profile-settings-field">
                        <label for="profile-name">Display name</label>
                        <input id="profile-name" name="name" type="text" value="{{ old('name', $user->name) }}" maxlength="120" autocomplete="name" required>
                    </div>
                    <div class="profile-settings-field">
                        <label for="profile-email">Email address</label>
                        <input id="profile-email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="254" autocomplete="email" required>
                    </div>
                    <div class="profile-settings-field">
                        <label for="profile-phone">Phone <span>Optional</span></label>
                        <input id="profile-phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" maxlength="30" autocomplete="tel">
                    </div>
                    <div class="profile-settings-field profile-settings-bio-field">
                        <label for="profile-bio">Creator bio <span>Optional</span></label>
                        <textarea id="profile-bio" name="bio" rows="4" maxlength="2000">{{ old('bio', $user->bio) }}</textarea>
                    </div>
                </div>
                <button class="profile-settings-primary-button" type="submit"><i class="bi bi-check2" aria-hidden="true"></i> Save profile</button>
            </form>
        </section>

        <div class="profile-settings-side-column">
            <section class="profile-settings-panel" aria-labelledby="password-heading">
                <div class="profile-settings-panel-heading">
                    <span class="profile-settings-panel-icon profile-settings-icon-cyan"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
                    <div><h2 id="password-heading">Change password</h2><p>Keep your account secure.</p></div>
                </div>
                <form class="profile-settings-form profile-settings-password-form" method="post" action="{{ route('user.password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="profile-settings-field">
                        <label for="current-password">Current password</label>
                        <input id="current-password" name="current_password" type="password" autocomplete="current-password" required>
                    </div>
                    <div class="profile-settings-field">
                        <label for="new-password">New password</label>
                        <input id="new-password" name="new_password" type="password" minlength="8" maxlength="128" autocomplete="new-password" required>
                    </div>
                    <div class="profile-settings-field">
                        <label for="new-password-confirmation">Confirm new password</label>
                        <input id="new-password-confirmation" name="new_password_confirmation" type="password" minlength="8" maxlength="128" autocomplete="new-password" required>
                    </div>
                    <small class="profile-settings-help">Use at least 8 characters.</small>
                    <button class="profile-settings-secondary-button" type="submit">Update password</button>
                </form>
            </section>
        </div>
    </div>
</main>
</body>
</html>
