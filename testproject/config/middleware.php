<?php
declare(strict_types=1);

use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Middleware\SessionMiddleware;
use App\Middleware\AuthMiddleware;

return function (App $app)
{
    // Add Error Middleware
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);

    $errorMiddleware->setErrorHandler(
        HttpNotFoundException::class, 
        function () use ($app) {
            $response = $app->getResponseFactory()->createResponse();
            return $response->withHeader('Location','/errors/forbidden',404);
        }
    );


    // Create Twig
    $twig = Twig::create(__DIR__ . '/../resources/templates', ['cache' => __DIR__ . '/../cache', 'debug' =>true]);

    // Add Twig-View Middleware
    $app->add(TwigMiddleware::create($app, $twig));
    
    $app->add(SessionMiddleware::class);

    $app->add(AuthMiddleware::class);
};