<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Pixmoree — Digital Collectibles Marketplace</title>
    <meta name="description" content="Learn about Pixmoree, a student-created digital collectibles marketplace concept for discovering and showcasing digital art.">
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
  <header class="about-hero">
    <div class="about-hero-copy">
      <span class="about-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> OUR STORY</span>
      <h1>A little more<br><span class="about-hero-brand-line">about <img class="about-hero-logo" src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree"></span></h1>
      <p>A colorful corner of the internet for discovering digital art, meeting creators, and finding something that feels uniquely yours.</p>
      <a class="about-hero-link" href="/#explore">Explore the gallery <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
    </div>
    <div class="about-hero-art">
      <img src="{{ asset('assets/anime-style-character-space (2).jpg') }}" alt="Dreamlike character artwork among the stars">
      <span><i class="bi bi-stars" aria-hidden="true"></i> Discover something new</span>
    </div>
  </header>

  <section class="about-content" aria-label="About Pixmoree">
    <article class="about-story-card">
      <div class="about-story-image">
        <img src="{{ asset('assets/guest-hero-background.png') }}" alt="A colorful digital universe, representing the Pixmoree marketplace">
        <span class="about-image-mark"><i class="bi bi-sparkle" aria-hidden="true"></i></span>
      </div>
      <div class="about-story-copy">
        <span class="about-eyebrow">01 / ABOUT PIXMOREE</span>
        <h2>Digital art, waiting to be discovered.</h2>
        <p>Pixmoree is a digital collectibles marketplace concept focused on discovering and showcasing digital artwork. It brings imaginative pieces together in one place, making it easy to browse art and see the people behind it.</p>
      </div>
    </article>

    <article class="about-story-card about-story-reverse">
      <div class="about-story-image">
        <img src="{{ asset('assets/Neon Pixel Sparkle Sticker Sheet.png') }}" alt="A sheet of playful neon pixel-art characters and sparkles">
        <span class="about-image-mark"><i class="bi bi-heart" aria-hidden="true"></i></span>
      </div>
      <div class="about-story-copy">
        <span class="about-eyebrow">02 / OUR MISSION</span>
        <h2>Make room for every kind of creator.</h2>
        <p>Our goal is to offer a simple, visually engaging place where people can explore digital artwork and discover different artists. We want the experience to feel welcoming, inspiring, and fun to come back to.</p>
      </div>
    </article>

    <article class="about-story-card">
      <div class="about-story-image">
        <img src="{{ asset('assets/29108282_JEMA GER 1446-01.jpg') }}" alt="Anime-inspired digital artwork from the Pixmoree gallery">
        <span class="about-image-mark"><i class="bi bi-grid" aria-hidden="true"></i></span>
      </div>
      <div class="about-story-copy">
        <span class="about-eyebrow">03 / WHAT YOU CAN DO</span>
        <h2>Explore at your own pace.</h2>
        <p>Browse collections, open artwork to see its details, check out what is trending, and save pieces you love to your favorites. Pixmoree is designed to make finding your next inspiration feel effortless.</p>
        <a class="about-inline-link" href="/#explore">Browse the gallery <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
      </div>
    </article>
  </section>

  <section class="about-project">
    <div class="about-project-image">
      <img src="{{ asset('assets/anime-character-using-virtual-reality-glasses-metaverse.jpg') }}" alt="A character exploring a virtual digital world">
      <div class="about-project-image-caption"><i class="bi bi-code-slash" aria-hidden="true"></i> Made while learning</div>
    </div>
    <div class="about-project-copy">
      <span class="about-eyebrow">04 / ABOUT THE PROJECT</span>
      <h2>Built as a learning experience.</h2>
      <p>Pixmoree is an educational project developed by students to practice web design, frontend development, and marketplace functionality. The site is a creative concept, made to bring those skills together in one interactive experience.</p>
      <p class="about-project-started"><i class="bi bi-calendar3" aria-hidden="true"></i> Project started <time datetime="2026-10-15">October 15, 2026</time></p>
      <div class="about-project-note"><i class="bi bi-lightbulb" aria-hidden="true"></i><span>Curiosity, creativity, and code — all in one project.</span></div>
      <div class="about-tools">
        <span class="about-tools-label">Apps used</span>
        <div class="about-tool-list">
          <span class="about-tool"><span class="about-tool-logo about-tool-ae" aria-hidden="true">Ae</span><span>After Effects</span></span>
          <span class="about-tool"><span class="about-tool-logo about-tool-ps" aria-hidden="true">Ps</span><span>Photoshop</span></span>
          <span class="about-tool">
            <svg class="about-tool-vscode" viewBox="0 0 24 24" aria-hidden="true">
              <path fill="#007ACC" d="M17.2 2.1 7.3 11.2 2.1 7.1 0 8.5v7l2.1 1.4 5.2-4.1 9.9 9.1L24 19V5l-6.8-2.9Zm.1 4.1 3.2 1.4v8.8l-3.2 1.4-6.7-5.8 6.7-5.8Z"/>
              <path fill="#1F9CF0" d="m2.1 7.1 5.2 4.1v1.6l-5.2 4.1L0 15.5v-7l2.1-1.4Z"/>
            </svg>
            <span>VS Code</span>
          </span>
        </div>
      </div>
    </div>
  </section>
  <section class="about-student-brief" aria-label="Student project overview">
      <div class="about-student-brief-heading"><span class="about-eyebrow">STUDENT PROJECT OVERVIEW</span><h3>Purpose, scope, and learning goals.</h3></div>
      <div class="about-student-brief-grid">
        <article><i class="bi bi-bullseye" aria-hidden="true"></i><h4>Project purpose</h4><p>Explore how a digital-art marketplace can help people discover artwork and creators through a clear, accessible interface.</p></article>
        <article><i class="bi bi-stars" aria-hidden="true"></i><h4>Core features</h4><p>Artwork discovery, search and filters, favorites, creator listings, user collections, NFT detail views, and simulated checkout.</p></article>
        <article><i class="bi bi-mortarboard" aria-hidden="true"></i><h4>Learning goals</h4><p>Practice responsive UI design, reusable page patterns, browser storage, form validation, accessibility, and responsible handling of creative work.</p></article>
        <article><i class="bi bi-code-slash" aria-hidden="true"></i><h4>Tools and stack</h4><p>PHP/Laravel-compatible views, HTML, CSS, JavaScript, and Bootstrap, with Photoshop and After Effects for visual assets.</p></article>
        <article><i class="bi bi-journal-check" aria-hidden="true"></i><h4>Scope and limitations</h4><p>This build is a student prototype. Accounts, listings, transactions, Pixel Coin, and payments are browser-based demonstrations—not production services.</p></article>
      </div>
  </section>

  <section class="about-team" aria-labelledby="about-team-title">
    <div class="about-team-heading">
      <span class="about-eyebrow">05 / MEET THE CREATORS</span>
      <h2 id="about-team-title">The people behind Pixmoree.</h2>
      <p>We’re a student team bringing design, code, and creativity together. Replace these profile placeholders with your team’s details.</p>
    </div>
    <div class="about-team-grid">
      <article class="about-team-card">
        <div class="about-team-image"><img src="{{ asset('assets/anime-style-character-space.jpg') }}" alt="Artwork placeholder for team member one"><span>01</span></div>
        <div class="about-team-details">
          <span class="about-team-role">Project Lead · UI/UX Designer</span>
          <h3>[Your Name]</h3>
          <p class="about-team-course">[Course / Program] <span>·</span> [Year Level]</p>
          <blockquote>“[Add a short quote about your role or what inspired you.]”</blockquote>
          <div class="about-team-contact">
            <span><i class="bi bi-instagram" aria-hidden="true"></i> @your-handle</span>
            <span><i class="bi bi-envelope" aria-hidden="true"></i> your.email@example.com</span>
            <span><i class="bi bi-telephone" aria-hidden="true"></i> +63 XXX XXX XXXX</span>
          </div>
        </div>
      </article>
      <article class="about-team-card">
        <div class="about-team-image"><img src="{{ asset('assets/29108282_JEMA GER 1446-01.jpg') }}" alt="Artwork placeholder for team member two"><span>02</span></div>
        <div class="about-team-details">
          <span class="about-team-role">Frontend Developer</span>
          <h3>[Your Name]</h3>
          <p class="about-team-course">[Course / Program] <span>·</span> [Year Level]</p>
          <blockquote>“[Add a short quote about your role or what inspired you.]”</blockquote>
          <div class="about-team-contact">
            <span><i class="bi bi-github" aria-hidden="true"></i> @your-handle</span>
            <span><i class="bi bi-envelope" aria-hidden="true"></i> your.email@example.com</span>
            <span><i class="bi bi-telephone" aria-hidden="true"></i> +63 XXX XXX XXXX</span>
          </div>
        </div>
      </article>
      <article class="about-team-card">
        <div class="about-team-image"><img src="{{ asset('assets/anime-style-character-space (1).jpg') }}" alt="Artwork placeholder for team member three"><span>03</span></div>
        <div class="about-team-details">
          <span class="about-team-role">Marketplace Developer</span>
          <h3>[Your Name]</h3>
          <p class="about-team-course">[Course / Program] <span>·</span> [Year Level]</p>
          <blockquote>“[Add a short quote about your role or what inspired you.]”</blockquote>
          <div class="about-team-contact">
            <span><i class="bi bi-instagram" aria-hidden="true"></i> @your-handle</span>
            <span><i class="bi bi-envelope" aria-hidden="true"></i> your.email@example.com</span>
            <span><i class="bi bi-telephone" aria-hidden="true"></i> +63 XXX XXX XXXX</span>
          </div>
        </div>
      </article>
      <article class="about-team-card">
        <div class="about-team-image"><img src="{{ asset('assets/anime-character-using-virtual-reality-glasses-metaverse.jpg') }}" alt="Artwork placeholder for team member four"><span>04</span></div>
        <div class="about-team-details">
          <span class="about-team-role">Content &amp; Visual Designer</span>
          <h3>[Your Name]</h3>
          <p class="about-team-course">[Course / Program] <span>·</span> [Year Level]</p>
          <blockquote>“[Add a short quote about your role or what inspired you.]”</blockquote>
          <div class="about-team-contact">
            <span><i class="bi bi-instagram" aria-hidden="true"></i> @your-handle</span>
            <span><i class="bi bi-envelope" aria-hidden="true"></i> your.email@example.com</span>
            <span><i class="bi bi-telephone" aria-hidden="true"></i> +63 XXX XXX XXXX</span>
          </div>
        </div>
      </article>
    </div>
    <p class="about-team-privacy"><i class="bi bi-shield-check" aria-hidden="true"></i> Only publish contact details and social profiles that each team member has agreed to share.</p>
  </section>

  <section class="about-contact">
    <div class="about-contact-icon"><i class="bi bi-chat-square-heart" aria-hidden="true"></i></div>
    <div>
      <span class="about-eyebrow">06 / CONTACT & PROJECT INFO</span>
      <h2>Have a question about the project?</h2>
      <p>For project questions, use the team contact details above once your members have chosen what they want to share, or reach out through your course or class channel.</p>
    </div>
    <a href="/" class="about-contact-link">Back to home <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
  </section>
</main>
<footer class="site-footer about-footer">
  <a href="/"><img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree" class="footer-logo"></a>
  <p>Discover a universe of digital creativity.</p>
  <div class="footer-links"><a href="/#explore">Explore</a><a href="/#creators">Creators</a><a href="/about" aria-current="page">About</a></div>
  <small>© 2026 Pixmoree. Made for creators and collectors.</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
