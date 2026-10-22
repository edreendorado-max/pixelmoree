<?php

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $settings = self::settings();
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $settings['host'],
            $settings['port'],
            $settings['database']
        );

        self::$pdo = new PDO($dsn, $settings['username'], $settings['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }

    private static function settings(): array
    {
        $defaults = [
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'pixelmoree',
            'username' => 'root',
            'password' => '',
        ];
        $environment = [];
        $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

        if (is_file($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                throw new RuntimeException('Unable to read the .env database configuration file.');
            }

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }

                [$key, $value] = explode('=', $line, 2);
                $environment[trim($key)] = trim(trim($value), "\"'");
            }
        }

        $keys = [
            'host' => 'DB_HOST',
            'port' => 'DB_PORT',
            'database' => 'DB_DATABASE',
            'username' => 'DB_USERNAME',
            'password' => 'DB_PASSWORD',
        ];

        foreach ($keys as $setting => $environmentKey) {
            $value = $_ENV[$environmentKey] ?? $_SERVER[$environmentKey] ?? getenv($environmentKey);
            if (function_exists('env')) {
                $frameworkValue = env($environmentKey);
                if ($frameworkValue !== null) {
                    $value = $frameworkValue;
                }
            }
            if ($value !== false && $value !== '') {
                $defaults[$setting] = $value;
            } elseif (array_key_exists($environmentKey, $environment)) {
                $defaults[$setting] = $environment[$environmentKey];
            }
        }

        return $defaults;
    }
}
