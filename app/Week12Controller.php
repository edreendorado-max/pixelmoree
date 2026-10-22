<?php

final class Week12Controller
{
    public function __construct(private readonly ArtworkRepository $artworks)
    {
    }

    public function dispatch(string $path, string $method): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['week12_csrf'] ??= bin2hex(random_bytes(32));
        $token = $_SESSION['week12_csrf'];

        if ($method === 'POST') {
            $this->handlePost($path, $token);
            return;
        }
        if ($method !== 'GET') {
            http_response_code(405);
            header('Allow: GET, POST');
            echo 'Method not allowed.';
            return;
        }

        if ($path === '/week12') {
            $this->render('week12/client/index.blade.php', 'Explore artwork', 'client', [
                'artworks' => $this->artworks->all(),
                'week12CsrfToken' => $token,
            ]);
            return;
        }

        if (preg_match('#^/week12/artworks/([a-z0-9-]+)$#', $path, $matches) === 1) {
            $artwork = $this->artworks->find($matches[1]);
            if ($artwork === null) {
                http_response_code(404);
                echo 'Artwork not found.';
                return;
            }

            $this->render('week12/client/show.blade.php', $artwork['title'], 'client', [
                'artwork' => $artwork,
                'week12CsrfToken' => $token,
            ]);
            return;
        }

        if ($path === '/week12/admin') {
            $this->render('week12/admin/dashboard.blade.php', 'Admin overview', 'admin', [
                'counts' => $this->artworks->counts(),
                'artworks' => $this->artworks->recent(),
                'week12CsrfToken' => $token,
            ]);
            return;
        }

        if ($path === '/week12/admin/artworks') {
            $this->render('week12/admin/artworks.blade.php', 'Artwork management', 'admin', [
                'artworks' => $this->artworks->all(),
                'error' => $_SESSION['week12_error'] ?? null,
                'week12CsrfToken' => $token,
            ]);
            unset($_SESSION['week12_error']);
            return;
        }

        http_response_code(404);
        echo 'Page not found.';
    }

    private function handlePost(string $path, string $token): void
    {
        if (!hash_equals($token, (string) ($_POST['_token'] ?? ''))) {
            http_response_code(419);
            echo 'The form expired. Reload the page and try again.';
            return;
        }

        try {
            if ($path === '/week12/admin/artworks') {
                $id = $this->artworks->create($_POST);
                $this->redirect('/week12/artworks/' . rawurlencode($id));
                return;
            }

            if (preg_match('#^/week12/admin/artworks/([a-z0-9-]+)/status$#', $path, $matches) === 1) {
                $this->artworks->updateStatus($matches[1], (string) ($_POST['status'] ?? ''));
                $this->redirect('/week12/admin/artworks');
                return;
            }

            if (preg_match('#^/week12/admin/artworks/([a-z0-9-]+)/delete$#', $path, $matches) === 1) {
                $this->artworks->delete($matches[1]);
                $this->redirect('/week12/admin/artworks');
                return;
            }
        } catch (InvalidArgumentException | OutOfBoundsException $exception) {
            $_SESSION['week12_error'] = $exception->getMessage();
            $this->redirect('/week12/admin/artworks');
            return;
        }

        http_response_code(404);
        echo 'Page not found.';
    }

    private function render(string $view, string $title, string $area, array $data): void
    {
        $week12Title = $title;
        $week12Area = $area;
        extract($data, EXTR_SKIP);
        require week12_view_path($view);
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path, true, 303);
    }
}
