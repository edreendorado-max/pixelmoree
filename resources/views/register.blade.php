<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $authContent['meta_title'] }}</title>
    <meta name="description" content="{{ $authContent['meta_description'] }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
    <link href="{{ asset('css/typography.css') }}?v=20261117" rel="stylesheet">
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

    @if ($errors->any())
      <div class="auth-feedback" role="alert">{{ $errors->first() }}</div>
    @endif
    <form class="auth-form" data-auth-form method="post" action="{{ route('register.store') }}">
      @csrf
      <label for="register-name">{{ $authContent['name_label'] }}</label>
      <input id="register-name" name="name" type="text" placeholder="{{ $authContent['name_placeholder'] }}" autocomplete="name" value="{{ old('name') }}" maxlength="120" required>
      <label for="register-username">Username <small>(optional)</small></label>
      <input id="register-username" name="username" type="text" placeholder="Choose a username" autocomplete="username" value="{{ old('username') }}" maxlength="30" pattern="[A-Za-z0-9._-]+" title="Use letters, numbers, periods, underscores, or hyphens.">
      <label for="register-email">{{ $authContent['email_label'] }}</label>
      <input id="register-email" name="email" type="email" placeholder="{{ $authContent['email_placeholder'] }}" autocomplete="email" value="{{ old('email') }}" required>
      <label for="register-password">{{ $authContent['password_label'] }}</label>
      <input id="register-password" name="password" type="password" placeholder="{{ $authContent['password_placeholder'] }}" autocomplete="new-password" minlength="6" pattern="(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}" title="At least 6 characters, with an uppercase letter, a number, and a special character." required>
      <label for="register-password-confirm">{{ $authContent['confirm_label'] }}</label>
      <input id="register-password-confirm" name="password_confirmation" type="password" placeholder="{{ $authContent['confirm_placeholder'] }}" autocomplete="new-password" minlength="6" required>
      <small>Password must be at least 6 characters and include an uppercase letter, a number, and a special character.</small>
      <label class="auth-check"><input type="checkbox" name="terms" required><span>{{ $authContent['terms_intro'] }} <a href="#terms">{{ $authContent['terms_label'] }}</a> {{ $authContent['privacy_connector'] }} <a href="#privacy">{{ $authContent['privacy_label'] }}</a>.</span></label>
      <button class="auth-submit" type="submit">{{ $authContent['submit_label'] }} <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
      <p class="auth-feedback" role="status" aria-live="polite" hidden></p>
    </form>
    <p class="auth-switch">{{ $authContent['switch_prompt'] }} <a href="/login">{{ $authContent['switch_link'] }}</a></p>
    <a class="auth-back" href="/"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ $authContent['back_label'] }}</a>
  </section>
</main>
</body>
</html>
