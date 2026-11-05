<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify your email — Pixmoree</title>
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
    <p class="auth-eyebrow"><i class="bi bi-envelope-check" aria-hidden="true"></i> EMAIL VERIFICATION</p>
    <h1 id="auth-title">Check your inbox</h1>
    <p class="auth-intro">Enter the 6-digit code sent to <strong>{{ $user->email }}</strong>. It expires in 10 minutes.</p>
    @if ($errors->any())
      <div class="auth-feedback" role="alert">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
      <div class="auth-feedback" role="status">{{ session('status') }}</div>
    @endif
    <form class="auth-form" method="post" action="{{ route('verification.verify') }}">
      @csrf
      <label for="verification-code">Verification code</label>
      <input id="verification-code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" required autofocus>
      <button class="auth-submit" type="submit">Verify email <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
    </form>
    <form method="post" action="{{ route('verification.resend') }}">
      @csrf
      <button class="auth-back" type="submit">Send a new code</button>
    </form>
    <p class="auth-switch">Already verified? <a href="{{ route('login') }}">Sign in</a></p>
  </section>
</main>
</body>
</html>
