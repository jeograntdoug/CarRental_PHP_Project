<?php
declare(strict_types=1);

use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Middleware\AuthMiddleware;

return function (App $app,Twig $twig)
{
    // Add Error Middleware
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);

    $errorMiddleware->setErrorHandler(
        HttpNotFoundException::class, 
        function () use ($app) {
            $response = $app->getResponseFactory()->createResponse();
            return $response->withHeader('Location','/errors/pagenotfound',404);
        }
    );

    // Add Twig-View Middleware
    $app->add(TwigMiddleware::create($app, $twig));
    $app->add(AuthMiddleware::class);
};