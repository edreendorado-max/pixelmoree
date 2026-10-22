<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestPath) ? $requestPath : '/';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
$scriptDirectory = str_replace('\\', '/', dirname($scriptName));
$basePath = strtolower(basename($scriptName)) === 'index.php' ? rtrim($scriptDirectory, '/') : '';
if ($basePath !== '' && str_starts_with($requestPath, $basePath . '/')) {
    $requestPath = substr($requestPath, strlen($basePath));
}
$rootPath = realpath(__DIR__);
$staticPath = realpath(__DIR__ . rawurldecode($requestPath));

if ($rootPath !== false && $staticPath !== false &&
    str_starts_with(strtolower($staticPath), strtolower($rootPath . DIRECTORY_SEPARATOR)) &&
    is_file($staticPath)) {
    return false;
}

header('Content-Type: text/html; charset=UTF-8');
$routePath = rtrim($requestPath, '/') ?: '/';
if ($routePath === '/week12' || str_starts_with($routePath, '/week12/')) {
    require_once __DIR__ . '/app/Week12Helpers.php';
    require_once __DIR__ . '/app/Database.php';
    require_once __DIR__ . '/app/ArtworkRepository.php';
    require_once __DIR__ . '/app/Week12Controller.php';

    try {
        $controller = new Week12Controller(new ArtworkRepository(Database::connection()));
        $controller->dispatch($routePath, $_SERVER['REQUEST_METHOD'] ?? 'GET');
    } catch (PDOException $exception) {
        error_log('Pixmoree MySQL connection failed: ' . $exception->getMessage());
        http_response_code(503);
        echo 'The Pixmoree database is unavailable. Start MySQL in XAMPP, import database/setup.sql, and check the local .env settings.';
    }

    exit;
}

if ($routePath === '/guest-copy') {
    $guestCopyPath = __DIR__ . '/guest-copy/index.html';
    if (!is_file($guestCopyPath) || !readfile($guestCopyPath)) {
        http_response_code(500);
        echo 'Unable to read the guest page copy.';
    }
    exit;
}
$isNftDetailRoute = preg_match('#^/(?:user/)?nft/[a-z0-9][a-z0-9-]*$#i', $routePath) === 1;
$pages = [
    '/' => 'home',
    '/about' => 'about',
    '/admin' => 'admin',
    '/user' => 'home',
    '/user/dashboard' => 'home',
    '/user/create' => 'home',
    '/user/settings' => 'home',
    '/user/profile-settings' => 'home',
    '/user/notifications' => 'home',
    '/user/about' => 'home',
    '/user/purchases' => 'home',
    '/user/nft-management' => 'home',
    '/user/collection' => 'home',
    '/user/favorites' => 'home',
    '/user/history' => 'home',
    '/user/profile' => 'home',
    '/user/payment' => 'home',
    '/user/terms' => 'home',
    '/login' => 'login',
    '/register' => 'register',
    '/create-account' => 'register',
];
$page = $isNftDetailRoute ? 'home' : ($pages[$routePath] ?? null);
if ($page === null) {
    http_response_code(404);
    echo 'Page not found.';
    exit;
}

$viewPath = __DIR__ . '/resources/views/' . $page . '.blade.php';
if (!is_file($viewPath)) {
    http_response_code(500);
    echo 'Missing view file.';
    exit;
}
$html = file_get_contents($viewPath);
if ($html === false) {
    http_response_code(500);
    echo 'Unable to read view file.';
    exit;
}
$html = preg_replace_callback("/{{\s*asset\(\s*'([^']+)'\s*\)\s*}}/", function ($m) {
    return '/public/' . $m[1];
}, $html);
echo $html;
