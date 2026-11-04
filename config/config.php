<?php

declare(strict_types=1);

// Values come from the environment (docker-compose.yml); defaults match the compose defaults.
return [
    'app' => [
        // Shows exception details on the 500 page. Never enable in production.
        'debug' => getenv('APP_DEBUG') === '1',
    ],
    'db' => [
        'host' => getenv('DB_HOST') ?: 'mysql',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'blog',
        'user' => getenv('DB_USER') ?: 'blog',
        'password' => getenv('DB_PASSWORD') ?: 'secret',
    ],
];
