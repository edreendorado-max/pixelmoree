<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose a new password | Pixmoree</title>
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
    <h1 id="auth-title">Choose a new password</h1>
    <p class="auth-intro">Use at least 6 characters, including an uppercase letter, a number, and a special character.</p>

    @if ($errors->any())
      <div class="auth-feedback" role="alert">{{ $errors->first() }}</div>
    @endif

    <form class="auth-form" method="post" action="{{ route('password.update') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <label for="reset-email">Email</label>
      <input id="reset-email" name="email" type="email" autocomplete="email" value="{{ old('email', $email) }}" required>
      <label for="reset-password">New password</label>
      <input id="reset-password" name="password" type="password" autocomplete="new-password" minlength="6" pattern="(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}" required>
      <label for="reset-password-confirmation">Confirm new password</label>
      <input id="reset-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="6" required>
      <button class="auth-submit" type="submit">Update password</button>
    </form>
    <a class="auth-back" href="{{ route('login') }}">Back to log in</a>
  </section>
</main>
</body>
</html>