<?php
declare(strict_types=1);

namespace App\Controller;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function(App $app) {
    // Routes

    $app->get('/', HomeController::class . ':home');
    $app->get('/register', HomeController::class . ':register');
    $app->get('/login', HomeController::class . ':login');

    $app->post('/login', AuthController::class . ':authenticate');
    $app->post('/user/create', UserController::class . ':create');

    

    //Routes for Errors Pages
    $app->group('/errors', function (RouteCollectorProxy $group) {
    // ??: Couldn't use /error url. Is it reserved??
        $group->get('/forbidden', ErrorController::class . ':forbidden');
        $group->get('/pagenotfound', ErrorController::class . ':pageNotFound');
        $group->get('/internal', ErrorController::class . ':internal');
    });

    $app->get('/search/location/', HomeController::class . ':searchLocation');
};