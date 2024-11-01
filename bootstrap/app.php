<?php

declare(strict_types=1);

use WaHelp\Core\Application;
use WaHelp\Core\Database;
use WaHelp\Core\DatabaseFactory;
use WaHelp\Core\Http\Request;
use WaHelp\Core\Routing\Router;
use WaHelp\Newsletter\Repository\NewsletterRepository;
use WaHelp\Newsletter\Repository\UserNewsletterRepository;
use WaHelp\Newsletter\Repository\UserRepository;
use WaHelp\Newsletter\Service\NewsletterService;
use WaHelp\Newsletter\Service\UserFileImporter;
use WaHelp\Newsletter\Service\UserNewsletterSender;
use WaHelp\Newsletter\Service\UserPaginator;

$configPath = __DIR__.'/../config.php';

if (!file_exists($configPath)) {
    throw new RuntimeException("You need to copy config.example.php to config.php.");
}

$config = require __DIR__.'/../config.php';
$routes = require __DIR__.'/routes.php';

$app = new Application(dirname(__DIR__).'/', $config);

// Basic
$app->singleton(Database::class, function (Application $app) {
    $config = $app->getConfig();

    if (isset($config['app']['database'])) {
        return (new DatabaseFactory())->create($config['app']['database']);
    }

    throw new RuntimeException('Database config not found.');
});

$app->singleton(Request::class, fn(Application $app) => Request::createFromGlobals());
$app->singleton(Router::class, fn(Application $app) => new Router($app, $routes));

// Repositories
$app->singleton(
    UserRepository::class,
    fn(Application $app) => new UserRepository($app->make(Database::class))
);

$app->singleton(
    NewsletterRepository::class,
    fn(Application $app) => new NewsletterRepository($app->make(Database::class))
);

$app->singleton(
    UserNewsletterRepository::class,
    fn(Application $app) => new UserNewsletterRepository($app->make(Database::class))
);

// Services
$app->singleton(UserFileImporter::class, function (Application $app) {
    return new UserFileImporter(
        $app->make(UserRepository::class)
    );
});

$app->singleton(NewsletterService::class, function (Application $app) {
    return new NewsletterService(
        $app->make(NewsletterRepository::class)
    );
});

$app->singleton(UserNewsletterSender::class, function (Application $app) {
    return new UserNewsletterSender(
        $app->make(UserNewsletterRepository::class),
        $app->make(Database::class)
    );
});

$app->singleton(UserPaginator::class, function (Application $app) {
    return new UserPaginator($app->make(UserRepository::class));
});


return $app;