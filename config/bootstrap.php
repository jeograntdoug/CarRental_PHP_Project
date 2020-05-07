<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;
use Slim\Views\Twig;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Create Twig
$twig = Twig::create(__DIR__ . '/../src/templates', ['cache' => __DIR__ . '/../cache', 'debug' =>true]);

require_once __DIR__ . '/settings.php';

// Register middleware
(require __DIR__ . '/middleware.php')($app, $twig);

// Register routes
(require __DIR__ . '/routes.php')($app);

return $app;