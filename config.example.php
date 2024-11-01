<?php

const ENV_PROD = 'prod';
const ENV_DEV = 'dev';

return [
    'app' => [
        'env' => ENV_DEV,
        'database' => [
            'db_host' => getenv('DB_HOST') ?? '127.0.0.1',
            'db_port' => getenv('DB_PORT') ?? 5432,
            'db_database' => getenv('DB_DATABASE') ?? 'wahelp',
            'db_username' => getenv('DB_USERNAME') ?? 'postgres',
            'db_password' => getenv('DB_PASSWORD') ?? '',
        ],
    ],
];