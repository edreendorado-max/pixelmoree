<?php

if (!function_exists('week12_view_path')) {
    function week12_view_path(string $path): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}

if (!function_exists('week12_escape')) {
    function week12_escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('week12_asset')) {
    function week12_asset(string $path): string
    {
        if (function_exists('asset')) {
            return asset($path);
        }

        return week12_url('/public/' . ltrim($path, '/'));
    }
}

if (!function_exists('week12_url')) {
    function week12_url(string $path): string
    {
        if (function_exists('url')) {
            return url($path);
        }

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
        $scriptDirectory = str_replace('\\', '/', dirname($scriptName));
        $basePath = strtolower(basename($scriptName)) === 'index.php' ? rtrim($scriptDirectory, '/') : '';

        return $basePath . '/' . ltrim($path, '/');
    }
}
