<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $aboutContent['meta_title'] }}</title>
    <meta name="description" content="{{ $aboutContent['meta_description'] }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
</head>
<body class="about-page">
<nav class="navbar navbar-expand-lg navbar-dark site-nav guest-nav">
  <div class="container-fluid px-3 px-lg-5">
    <a class="navbar-brand" href="/" aria-label="Pixmoree home"><img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree" class="brand-logo"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="mainNav">
      <div class="guest-links mx-lg-auto my-3 my-lg-0">
        <a href="/">Home</a><a href="/#explore">Gallery</a><a href="/#trending">Trending</a><a class="active" href="/about" aria-current="page">About</a>
      </div>
      <div class="nav-actions"><a class="login-link" href="/login">Log in</a></div>
    </div>
  </div>
</nav>
<main>
  @include('partials.about-content', ['aboutContent' => $aboutContent, 'galleryUrl' => '/#explore', 'teamCreators' => $teamCreators])
</main>
<footer class="site-footer about-footer">
  <a href="/"><img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree" class="footer-logo"></a>
  <p>{{ $aboutContent['footer_tagline'] }}</p>
  <div class="footer-links"><a href="/#explore">Explore</a><a href="/#creators">Creators</a><a href="/about" aria-current="page">About</a></div>
  <small>© {{ date('Y') }} Pixmoree. Made for creators and collectors.</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
