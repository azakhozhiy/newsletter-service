<?php

declare(strict_types=1);

use WaHelp\Core\Application;
use WaHelp\Core\Routing\Router;
use WaHelp\Newsletter\Http\ExceptionHandler;

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require __DIR__.'/../vendor/autoload.php';

try {
    /** @var Application $app */
    $app = require __DIR__.'/../bootstrap/app.php';

    /** @var Router $router */
    $router = $app->make(Router::class);

    $router->dispatch()->send();
} catch (Throwable $e) {
    (new ExceptionHandler())->handle($e)->send();
}