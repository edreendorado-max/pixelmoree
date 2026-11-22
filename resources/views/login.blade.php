<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $authContent['meta_title'] }}</title>
    <meta name="description" content="{{ $authContent['meta_description'] }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}?v=20261127-creator-ui" rel="stylesheet">
    <link href="{{ asset('css/typography.css') }}?v=20261127-creator-ui" rel="stylesheet">
    <link href="{{ asset('css/notifications.css') }}?v=20261117" rel="stylesheet">
</head>
<body class="auth-page">
<main class="auth-shell">
  <section class="auth-card" aria-labelledby="auth-title">
    <a class="auth-brand" href="/" aria-label="Pixmoree home">
      <img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree">
    </a>
    <p class="auth-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> {{ $authContent['eyebrow'] }}</p>
    <h1 id="auth-title">{{ $authContent['heading'] }}</h1>
    <p class="auth-intro">{{ $authContent['intro'] }}</p>

    @if ($errors->any())
      <div class="auth-feedback" role="alert">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
      <div class="auth-feedback" role="status">{{ session('status') }}</div>
    @endif
    <form class="auth-form" data-auth-form method="post" action="{{ route('login.store') }}">
      @csrf
      <label for="login-email">Email or username</label>
      <input id="login-email" name="email" type="text" placeholder="Email or username" autocomplete="username" value="{{ old('email') }}" required>
      <div class="auth-password-label"><label for="login-password">{{ $authContent['password_label'] }}</label><a href="{{ route('password.request') }}">{{ $authContent['forgot_password'] }}</a></div>
      <input id="login-password" name="password" type="password" placeholder="{{ $authContent['password_placeholder'] }}" autocomplete="current-password" required>
      <button class="auth-submit" type="submit">{{ $authContent['submit_label'] }} <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
      <p class="auth-feedback" role="status" aria-live="polite" hidden></p>
    </form>
    <p class="auth-switch">{{ $authContent['switch_prompt'] }} <a href="/register">{{ $authContent['switch_link'] }}</a></p>
    <p class="auth-legal">{{ $authContent['legal_intro'] }} <a href="#terms">{{ $authContent['terms_label'] }}</a> {{ $authContent['privacy_connector'] }} <a href="#privacy">{{ $authContent['privacy_label'] }}</a>.</p>
    <a class="auth-back" href="/"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ $authContent['back_label'] }}</a>
  </section>
</main>
@include('partials.notification-popup', ['notification' => session('notification_popup')])
</body>
</html>
