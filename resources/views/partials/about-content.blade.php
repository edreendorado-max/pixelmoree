<header class="about-hero">
  <div class="about-hero-copy">
    <span class="about-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> {{ $aboutContent['hero']['eyebrow'] }}</span>
    <h1>{{ $aboutContent['hero']['heading'] }}<br><span class="about-hero-brand-line">about <img class="about-hero-logo" src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree"></span></h1>
    <p>{{ $aboutContent['hero']['description'] }}</p>
    <a class="about-hero-link" href="{{ $galleryUrl }}">{{ $aboutContent['hero']['link'] }} <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
  </div>
  <div class="about-hero-art">
    <img src="{{ asset($aboutContent['hero']['image']) }}" alt="{{ $aboutContent['hero']['image_alt'] }}">
    <span><i class="bi bi-stars" aria-hidden="true"></i> {{ $aboutContent['hero']['caption'] }}</span>
  </div>
</header>

<section class="about-content" aria-label="About Pixmoree">
  @foreach ($aboutContent['stories'] as $index => $story)
    <article class="about-story-card {{ $index === 1 ? 'about-story-reverse' : '' }}">
      <div class="about-story-image">
        <img src="{{ asset($story['image']) }}" alt="{{ $story['image_alt'] }}">
        <span class="about-image-mark"><i class="bi {{ ['bi-sparkle', 'bi-heart', 'bi-grid'][$index] }}" aria-hidden="true"></i></span>
      </div>
      <div class="about-story-copy">
        <span class="about-eyebrow">{{ $story['eyebrow'] }}</span>
        <h2>{{ $story['heading'] }}</h2>
        <p>{{ $story['body'] }}</p>
        @if ($index === 2)
          <a class="about-inline-link" href="{{ $galleryUrl }}">Browse the gallery <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        @endif
      </div>
    </article>
  @endforeach
</section>

<section class="about-project">
  <div class="about-project-image">
    <img src="{{ asset($aboutContent['project']['image']) }}" alt="{{ $aboutContent['project']['image_alt'] }}">
    <div class="about-project-image-caption"><i class="bi bi-code-slash" aria-hidden="true"></i> {{ $aboutContent['project']['caption'] }}</div>
  </div>
  <div class="about-project-copy">
    <span class="about-eyebrow">{{ $aboutContent['project']['eyebrow'] }}</span>
    <h2>{{ $aboutContent['project']['heading'] }}</h2>
    <p>{{ $aboutContent['project']['body'] }}</p>
    @if ($aboutContent['project']['started_at'] !== '')
      <p class="about-project-started"><i class="bi bi-calendar3" aria-hidden="true"></i> Project started <time datetime="{{ $aboutContent['project']['started_at'] }}">{{ \Illuminate\Support\Carbon::parse($aboutContent['project']['started_at'])->format('F j, Y') }}</time></p>
    @endif
    <div class="about-project-note"><i class="bi bi-lightbulb" aria-hidden="true"></i><span>{{ $aboutContent['project']['note'] }}</span></div>
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
  <div class="about-student-brief-heading">
    <span class="about-eyebrow">{{ $aboutContent['overview']['eyebrow'] }}</span>
    <h3>{{ $aboutContent['overview']['heading'] }}</h3>
  </div>
  <div class="about-student-brief-grid">
    @foreach ($aboutContent['overview']['items'] as $index => $item)
      <article>
        <i class="bi {{ ['bi-bullseye', 'bi-stars', 'bi-mortarboard', 'bi-code-slash', 'bi-journal-check'][$index] }}" aria-hidden="true"></i>
        <h4>{{ $item['heading'] }}</h4>
        <p>{{ $item['body'] }}</p>
      </article>
    @endforeach
  </div>
</section>

<section class="about-team" aria-labelledby="about-team-title">
  <div class="about-team-heading">
    <span class="about-eyebrow">{{ $aboutContent['team']['eyebrow'] }}</span>
    <h2 id="about-team-title">{{ $aboutContent['team']['heading'] }}</h2>
    <p>{{ $aboutContent['team']['intro'] }}</p>
  </div>
  <div class="about-team-grid">
    @foreach ($teamCreators as $member)
      <article class="about-team-card">
        <div class="about-team-image">
          <img src="{{ asset($member->avatar_path) }}" alt="{{ $member->name }}">
          <span>{{ sprintf('%02d', $loop->iteration) }}</span>
        </div>
        <div class="about-team-details">
          <span class="about-team-role">PIXMOREE CREATOR</span>
          <h3>{{ $member->name }}</h3>
          <p class="about-team-course">{{ $member->artworks_count }} {{ \Illuminate\Support\Str::plural('artwork', $member->artworks_count) }} in the gallery</p>
          @if ($member->bio)
            <blockquote>{{ $member->bio }}</blockquote>
          @endif
        </div>
      </article>
    @endforeach
  </div>
  <p class="about-team-privacy"><i class="bi bi-shield-check" aria-hidden="true"></i> Creator photos and artwork counts are connected to the Pixmoree database.</p>
</section>

<section class="about-contact">
  <div class="about-contact-icon"><i class="bi bi-chat-square-heart" aria-hidden="true"></i></div>
  <div>
    <span class="about-eyebrow">{{ $aboutContent['contact']['eyebrow'] }}</span>
    <h2>{{ $aboutContent['contact']['heading'] }}</h2>
    <p>{{ $aboutContent['contact']['body'] }}</p>
  </div>
  <a href="{{ $galleryUrl }}" class="about-contact-link">Back to home <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
</section>
