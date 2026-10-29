<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $authContent['meta_title'] }}</title>
    <meta name="description" content="{{ $authContent['meta_description'] }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
</head>
<body class="auth-page">
<main class="auth-shell">
  <section class="auth-card auth-card-register" aria-labelledby="auth-title">
    <a class="auth-brand" href="/" aria-label="Pixmoree home">
      <img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree">
    </a>
    <p class="auth-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> {{ $authContent['eyebrow'] }}</p>
    <h1 id="auth-title">{{ $authContent['heading'] }}</h1>
    <p class="auth-intro">{{ $authContent['intro'] }}</p>

    <div class="auth-socials" aria-label="Social sign-in options">
      <button class="auth-social-button" type="button" data-auth-provider="Google">
        <svg viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 24.5c0-1.4-.1-2.8-.4-4.1H24v7.8h11a9.4 9.4 0 0 1-4.1 6.2v5.1h6.7c3.9-3.6 6-8.8 6-15Z"/><path fill="#FF3D00" d="M24 44c5.5 0 10.1-1.8 13.5-4.8l-6.7-5.1c-1.9 1.3-4.1 2-6.8 2-5.2 0-9.6-3.5-11.2-8.2H5.9v5.2A20 20 0 0 0 24 44Z"/><path fill="#4CAF50" d="M12.8 27.9a12 12 0 0 1 0-7.8v-5.2H5.9a20 20 0 0 0 0 18.2l6.9-5.2Z"/><path fill="#1976D2" d="M24 11.9c3 0 5.7 1 7.8 3.1l5.8-5.8A19.4 19.4 0 0 0 24 4 20 20 0 0 0 5.9 14.9l6.9 5.2c1.6-4.7 6-8.2 11.2-8.2Z"/></svg>
        {{ $authContent['google_label'] }}
      </button>
      <button class="auth-social-button" type="button" data-auth-provider="Facebook">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#1877F2" d="M24 12a12 12 0 1 0-13.9 11.9v-8.4H7.1V12h3V9.3c0-3 1.8-4.7 4.6-4.7 1.3 0 2.7.2 2.7.2v3h-1.5c-1.5 0-2 .9-2 1.9V12h3.4l-.5 3.5h-2.9v8.4A12 12 0 0 0 24 12Z"/><path fill="#fff" d="m16.8 15.5.5-3.5h-3.4V9.7c0-1 .5-1.9 2-1.9h1.5v-3s-1.4-.2-2.7-.2c-2.8 0-4.6 1.7-4.6 4.7V12h-3v3.5h3v8.4a12 12 0 0 0 3.8 0v-8.4h2.9Z"/></svg>
        {{ $authContent['facebook_label'] }}
      </button>
    </div>

    <div class="auth-divider"><span>{{ $authContent['divider_label'] }}</span></div>
    @if ($errors->any())
      <div class="auth-feedback" role="alert">{{ $errors->first() }}</div>
    @endif
    <form class="auth-form" data-auth-form method="post" action="{{ route('register.store') }}">
      @csrf
      <label for="register-name">{{ $authContent['name_label'] }}</label>
      <input id="register-name" name="name" type="text" placeholder="{{ $authContent['name_placeholder'] }}" autocomplete="name" value="{{ old('name') }}" maxlength="120" required>
      <label for="register-email">{{ $authContent['email_label'] }}</label>
      <input id="register-email" name="email" type="email" placeholder="{{ $authContent['email_placeholder'] }}" autocomplete="email" value="{{ old('email') }}" required>
      <label for="register-password">{{ $authContent['password_label'] }}</label>
      <input id="register-password" name="password" type="password" placeholder="{{ $authContent['password_placeholder'] }}" autocomplete="new-password" minlength="8" required>
      <label for="register-password-confirm">{{ $authContent['confirm_label'] }}</label>
      <input id="register-password-confirm" name="password_confirmation" type="password" placeholder="{{ $authContent['confirm_placeholder'] }}" autocomplete="new-password" minlength="8" required>
      <label class="auth-check"><input type="checkbox" name="terms" required><span>{{ $authContent['terms_intro'] }} <a href="#terms">{{ $authContent['terms_label'] }}</a> {{ $authContent['privacy_connector'] }} <a href="#privacy">{{ $authContent['privacy_label'] }}</a>.</span></label>
      <button class="auth-submit" type="submit">{{ $authContent['submit_label'] }} <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
      <p class="auth-feedback" role="status" aria-live="polite" hidden></p>
    </form>
    <p class="auth-switch">{{ $authContent['switch_prompt'] }} <a href="/login">{{ $authContent['switch_link'] }}</a></p>
    <a class="auth-back" href="/"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ $authContent['back_label'] }}</a>
  </section>
</main>
<script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>
