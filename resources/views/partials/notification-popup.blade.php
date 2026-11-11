@if (is_array($notification ?? null) && isset($notification['title'], $notification['message']))
    <dialog class="app-notification" data-notification-popup aria-labelledby="notification-popup-title" aria-describedby="notification-popup-message">
        <div class="app-notification-icon" aria-hidden="true"><i class="bi bi-stars"></i></div>
        <h2 id="notification-popup-title">{{ $notification['title'] }}</h2>
        <p id="notification-popup-message">{{ $notification['message'] }}</p>
        <button type="button" class="app-notification-close" data-notification-close>Continue</button>
    </dialog>
    <script src="{{ asset('js/notifications.js') }}?v=20261111"></script>
@endif
