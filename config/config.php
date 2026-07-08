<?php

// A simple function to load .env file manually
if (!function_exists('loadEnv')) {
    function loadEnv($filePath)
    {
        if (!file_exists($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Load the .env file from the root directory
loadEnv(__DIR__ . '/../.env');

return [
    'app' => [
        'url' => rtrim($_ENV['BASE_URL'] ?? '/ohemaadetergents', '/'),
    ],
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'database' => $_ENV['DB_DATABASE'] ?? 'ohemaa_db',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
    ],
    'paystack' => [
        'secret_key' => $_ENV['PAYSTACK_SECRET_KEY'] ?? '',
        'public_key' => $_ENV['PAYSTACK_PUBLIC_KEY'] ?? '',
    ]
];
