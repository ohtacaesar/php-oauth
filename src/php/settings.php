<?php

$ini = parse_ini_file('/var/run/secrets/secrets.ini', true);

$isDevelopment = boolval($_ENV['DEVELOPMENT'] ?? false);

$settings = [
    'settings' => array_merge([
        'app' => [
            'title' => $_ENV['PHP_AUTH_TITLE'] ?? 'PHP Auth'
        ],
        'development' => $isDevelopment,
        'displayErrorDetails' => $isDevelopment, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        // Renderer settings
        'view' => [
            'template_path' => __DIR__ . '/../templates/',
            'debug' => $isDevelopment,
        ],
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['DOCKER']) ? 'php://stdout' : __DIR__ . '/../../logs/app.log',
        ],
        'pdo' => [
            'dsn' => $_ENV['PDO_DSN'] ?? 'pgsql:host=postgres;port=5432;dbname=postgres',
            'username' => $_ENV['PDO_USERNAME'] ?? 'postgres',
            'passwd' => $_ENV['PDO_PASSWORD'] ?? '',
        ],
        'cookie' => [
            'cookie_secure' => boolval($_ENV['COOKIE_SECURE']) ?? boolval($_SERVER['HTTPS']),
            'cookie_httponly' => boolval($_ENV['COOKIE_HTTPONLY']) ?? true,
        ],
        // OAuthの認証情報で自動的にロールを付与するルール
        'grantRules' => [
            \Util\Providers::GITHUB => [
                'id' => [
                    1635983 => ['ADMIN']
                ]
            ]
        ],
    ], $ini)
];

return $settings;
