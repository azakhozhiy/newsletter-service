<?php

use WaHelp\Core\Routing\Router;
use WaHelp\Newsletter\Http\Controller\NewsletterController;
use WaHelp\Newsletter\Http\Controller\UserController;

return [
    'users' => [
        'controller' => UserController::class,
        'actions' => [
            'import' => [
                'method_type' => Router::POST,
                'method' => 'import',
            ],
            'list' => [
                'method_type' => Router::GET,
                'method' => 'getList',
            ],
        ],
    ],
    'newsletter' => [
        'controller' => NewsletterController::class,
        'actions' => [
            'send' => [
                'method_type' => Router::POST,
                'method' => 'send',
            ],
        ],
    ],
];