<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile settings — Pixmoree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}?v=20261117-profile-images" rel="stylesheet">
    <link href="{{ asset('css/notifications.css') }}?v=20261117" rel="stylesheet">
    <link href="{{ asset('css/payment-checkout.css') }}" rel="stylesheet">
    <link href="{{ asset('css/typography.css') }}?v=20261117" rel="stylesheet">
    <link href="{{ asset('css/profile-image-editor.css') }}?v=20261117" rel="stylesheet">
</head>
<body class="profile-settings-page">
<header class="profile-settings-topbar">
    <div class="profile-settings-topbar-inner">
        <a class="profile-settings-brand" href="{{ route('user') }}" aria-label="Pixmoree account home">
            <img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree">
        </a>
        <nav class="profile-settings-nav" aria-label="Account navigation">
            <a href="{{ route('store') }}"><i class="bi bi-compass" aria-hidden="true"></i> Store</a>
            <a class="profile-settings-back-link" href="/user"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to account</a>
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
            <span class="profile-settings-account-avatar" data-image-preview="avatar">
                @if ($user->avatar_path)
                    <img data-image-preview-image src="{{ asset($user->avatar_path) }}" alt="">
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
                    <div class="profile-settings-banner-preview" data-image-preview="banner">
                        @if ($user->banner_path)
                            <img data-image-preview-image src="{{ asset($user->banner_path) }}" alt="Current profile banner">
                        @endif
                        <span><i class="bi bi-stars" aria-hidden="true"></i> PROFILE BANNER</span>
                    </div>
                    <div class="profile-settings-field">
                        <label for="profile-banner">Upload a profile banner</label>
                        <input id="profile-banner" name="banner" type="file" accept=".jpg,.jpeg,.png,.gif,.webp" data-image-crop="banner" aria-describedby="profile-banner-help">
                        <small id="profile-banner-help">JPG, PNG, GIF, or WebP. Maximum 10 MB. You can position and zoom the 3:1 crop before saving.</small>
                    </div>
                </div>
                <div class="profile-settings-avatar-row">
                    <span class="profile-settings-avatar-preview" data-image-preview="avatar">
                        @if ($user->avatar_path)
                            <img data-image-preview-image src="{{ asset($user->avatar_path) }}" alt="Current profile image">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </span>
                    <div class="profile-settings-field profile-settings-avatar-field">
                        <label for="profile-avatar">Creator profile image</label>
                        <input id="profile-avatar" name="avatar" type="file" accept=".jpg,.jpeg,.png,.gif,.webp" data-image-crop="avatar" aria-describedby="profile-avatar-help">
                        <small id="profile-avatar-help">JPG, PNG, GIF, or WebP. Maximum 5 MB. You can position and zoom the square crop before saving.</small>
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
                        <input id="new-password" name="new_password" type="password" minlength="6" maxlength="128" pattern="(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}" title="At least 6 characters, with an uppercase letter, a number, and a special character." autocomplete="new-password" required>
                    </div>
                    <div class="profile-settings-field">
                        <label for="new-password-confirmation">Confirm new password</label>
                        <input id="new-password-confirmation" name="new_password_confirmation" type="password" minlength="6" maxlength="128" autocomplete="new-password" required>
                    </div>
                    <small class="profile-settings-help">Use at least 6 characters, including an uppercase letter, a number, and a special character. This updates your password in-app; no email is sent.</small>
                    <button class="profile-settings-secondary-button" type="submit">Update password</button>
                </form>
            </section>
        </div>
    </div>
</main>
<dialog class="profile-image-crop-dialog" data-image-crop-dialog aria-labelledby="profile-image-crop-title" aria-describedby="profile-image-crop-help">
    <div class="profile-image-crop-heading">
        <div>
            <p class="profile-image-crop-eyebrow">IMAGE PREVIEW</p>
            <h2 id="profile-image-crop-title">Adjust your image</h2>
        </div>
        <button class="profile-image-crop-dismiss" type="button" data-image-crop-cancel aria-label="Cancel image crop">&times;</button>
    </div>
    <p class="profile-image-crop-help" id="profile-image-crop-help">Drag the image to position it, then use the zoom control to frame your preview. Cropped images are saved as JPEG; animated GIFs become a still image.</p>
    <div class="profile-image-crop-viewport" data-image-crop-viewport>
        <img data-image-crop-image alt="" draggable="false">
    </div>
    <label class="profile-image-crop-zoom-label" for="profile-image-crop-zoom">
        Zoom <output data-image-crop-zoom-value for="profile-image-crop-zoom">100%</output>
    </label>
    <input class="profile-image-crop-zoom" id="profile-image-crop-zoom" data-image-crop-zoom type="range" min="1" max="3" step="0.01" value="1">
    <p class="profile-image-crop-error" data-image-crop-error role="alert" hidden></p>
    <div class="profile-image-crop-actions">
        <button class="profile-image-crop-cancel" type="button" data-image-crop-cancel>Cancel</button>
        <button class="profile-image-crop-apply" type="button" data-image-crop-apply>Use this crop</button>
    </div>
</dialog>
<script src="{{ asset('js/profile-image-editor.js') }}?v=20261117" defer></script>
<script src="{{ asset('js/image-previews.js') }}?v=20261119" defer></script>
@include('partials.notification-popup', ['notification' => session('notification_popup')])
</body>
</html>
