<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset password | Pixmoree</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
    <link href="{{ asset('css/typography.css') }}?v=20261111" rel="stylesheet">
</head>
<body class="auth-page">
<main class="auth-shell">
  <section class="auth-card" aria-labelledby="auth-title">
    <a class="auth-brand" href="/" aria-label="Pixmoree home">
      <img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree">
    </a>
    <p class="auth-eyebrow">ACCOUNT RECOVERY</p>
    <h1 id="auth-title">Reset password</h1>
    <p class="auth-intro">Enter your account email and we will send a reset link if it matches an account.</p>

    @if (session('status'))
      <div class="auth-feedback" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
      <div class="auth-feedback" role="alert">{{ $errors->first() }}</div>
    @endif

    <form class="auth-form" method="post" action="{{ route('password.email') }}">
      @csrf
      <label for="reset-email">Email</label>
      <input id="reset-email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required>
      <button class="auth-submit" type="submit">Send reset link</button>
    </form>
    <a class="auth-back" href="{{ route('login') }}">Back to log in</a>
  </section>
</main>
</body>
</html>