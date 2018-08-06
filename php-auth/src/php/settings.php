<?php

$ini = parse_ini_file('/var/run/secrets/secrets.ini', true);

$isDevelopment = boolval($_ENV['development'] ?? false);

$settings = [
    'settings' => array_merge([
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
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../../logs/app.log',
        ],
        'pdo' => [
            'dsn' => 'pgsql:host=postgres;port=5432;dbname=postgres',
            'username' => 'postgres',
            'passwd' => '',
        ],
        // OAuthの認証情報で自動的にロールを付与するルール
        'grantRules' => [
            \Util\Providers::GITHUB => [
                'id' => [
                    1635983 => ['ADMIN']
                ]
            ]
        ]
    ], $ini)
];

return $settings;
