<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in — Pixmoree</title>
    <meta name="description" content="Log in to your Pixmoree account and continue exploring digital art.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
</head>
<body class="auth-page">
<main class="auth-shell">
  <section class="auth-card" aria-labelledby="auth-title">
    <a class="auth-brand" href="/" aria-label="Pixmoree home">
      <img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree">
    </a>
    <p class="auth-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> YOUR NEXT DIMENSION AWAITS</p>
    <h1 id="auth-title">Welcome back</h1>
    <p class="auth-intro">Log in to pick up where your creativity left off.</p>

    @if ($errors->any())
      <div class="auth-feedback" role="alert">{{ $errors->first() }}</div>
    @endif
    <form class="auth-form" data-auth-form method="post" action="{{ route('login.store') }}">
      @csrf
      <label for="login-email">Email address</label>
      <input id="login-email" name="email" type="email" placeholder="you@example.com" autocomplete="email" value="{{ old('email') }}" required>
      <div class="auth-password-label"><label for="login-password">Password</label><a href="#" data-auth-provider="Password reset">Forgot password?</a></div>
      <input id="login-password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
      <button class="auth-submit" type="submit">Log in <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
      <p class="auth-feedback" role="status" aria-live="polite" hidden></p>
    </form>
    <div class="auth-divider"><span>or continue with</span></div>
    <div class="auth-socials" aria-label="Social sign-in options">
      <button class="auth-social-button" type="button" data-auth-provider="Google">
        <svg viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 24.5c0-1.4-.1-2.8-.4-4.1H24v7.8h11a9.4 9.4 0 0 1-4.1 6.2v5.1h6.7c3.9-3.6 6-8.8 6-15Z"/><path fill="#FF3D00" d="M24 44c5.5 0 10.1-1.8 13.5-4.8l-6.7-5.1c-1.9 1.3-4.1 2-6.8 2-5.2 0-9.6-3.5-11.2-8.2H5.9v5.2A20 20 0 0 0 24 44Z"/><path fill="#4CAF50" d="M12.8 27.9a12 12 0 0 1 0-7.8v-5.2H5.9a20 20 0 0 0 0 18.2l6.9-5.2Z"/><path fill="#1976D2" d="M24 11.9c3 0 5.7 1 7.8 3.1l5.8-5.8A19.4 19.4 0 0 0 24 4 20 20 0 0 0 5.9 14.9l6.9 5.2c1.6-4.7 6-8.2 11.2-8.2Z"/></svg>
        Continue with Google
      </button>
      <button class="auth-social-button" type="button" data-auth-provider="Facebook">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#1877F2" d="M24 12a12 12 0 1 0-13.9 11.9v-8.4H7.1V12h3V9.3c0-3 1.8-4.7 4.6-4.7 1.3 0 2.7.2 2.7.2v3h-1.5c-1.5 0-2 .9-2 1.9V12h3.4l-.5 3.5h-2.9v8.4A12 12 0 0 0 24 12Z"/><path fill="#fff" d="m16.8 15.5.5-3.5h-3.4V9.7c0-1 .5-1.9 2-1.9h1.5v-3s-1.4-.2-2.7-.2c-2.8 0-4.6 1.7-4.6 4.7V12h-3v3.5h3v8.4a12 12 0 0 0 3.8 0v-8.4h2.9Z"/></svg>
        Continue with Facebook
      </button>
    </div>
    <p class="auth-switch">New to Pixmoree? <a href="/register">Create an account</a></p>
    <p class="auth-legal">By continuing, you agree to Pixmoree’s <a href="#terms">Terms</a> and <a href="#privacy">Privacy Policy</a>.</p>
    <a class="auth-back" href="/"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to Pixmoree</a>
  </section>
</main>
<script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>
