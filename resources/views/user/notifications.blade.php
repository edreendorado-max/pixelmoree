<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notifications | Pixmoree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}?v=20261122-notifications" rel="stylesheet">
    <link href="{{ asset('css/typography.css') }}?v=20261127-creator-ui" rel="stylesheet">
</head>
<body class="profile-settings-page">
<header class="profile-settings-topbar">
    <div class="profile-settings-topbar-inner">
        <a class="profile-settings-brand" href="{{ route('user') }}" aria-label="Pixmoree account home">
            <img src="{{ asset('assets/Pixmoree Neon Pixel Logo.png') }}" alt="Pixmoree">
        </a>
        <nav class="profile-settings-nav" aria-label="Account navigation">
            <a href="{{ route('store') }}"><i class="bi bi-shop-window" aria-hidden="true"></i> Store</a>
            <a class="profile-settings-back-link" href="{{ route('user') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to account</a>
        </nav>
    </div>
</header>

<main class="profile-settings-main">
    <header class="profile-settings-heading">
        <div>
            <p class="profile-settings-eyebrow"><i class="bi bi-bell" aria-hidden="true"></i> ACCOUNT ACTIVITY</p>
            <h1>Notifications</h1>
            <p>{{ $unreadNotificationCount }} unread notification{{ $unreadNotificationCount === 1 ? '' : 's' }}.</p>
        </div>
        @if ($unreadNotificationCount > 0)
            <form method="post" action="{{ route('user.notifications.read-all') }}">
                @csrf
                <button class="member-notifications-read" type="submit"><i class="bi bi-check2-all" aria-hidden="true"></i> Mark all as read</button>
            </form>
        @endif
    </header>

    @if (session('status'))
        <div class="profile-settings-notice profile-settings-notice-success" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i>{{ session('status') }}</div>
    @endif

    <section class="profile-settings-panel" aria-label="Your notifications">
        <div class="member-notification-list">
            @forelse ($notifications as $notification)
                <article class="member-notification-item {{ $notification->read_at ? 'is-read' : '' }}">
                    <span class="member-notification-icon" aria-hidden="true"><i class="bi bi-bell"></i></span>
                    <div class="member-notification-copy">
                        <strong>{{ $notification->data['title'] ?? 'Notification' }}</strong>
                        <small><time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time> · {{ $notification->read_at ? 'Read' : 'Unread' }}</small>
                        <p>{{ $notification->data['message'] ?? '' }}</p>
                        @if (!empty($notification->data['url']))
                            <a href="{{ $notification->data['url'] }}">Open details</a>
                        @endif
                    </div>
                    @if ($notification->read_at === null)
                        <form method="post" action="{{ route('user.notifications.read', $notification->id) }}">
                            @csrf
                            <button class="member-notification-mark" type="submit">Mark as read</button>
                        </form>
                    @endif
                </article>
            @empty
                <p class="member-empty-state">You have no notifications yet.</p>
            @endforelse
        </div>
        @if ($notifications instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-4">{{ $notifications->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
</main>
</body>
</html>
