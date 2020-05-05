<?php
declare(strict_types=1);

namespace App\Controller;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function(App $app){
    // Routes

    $app->get('/', HomeController::class . ':home');
    $app->get('/hello', HomeController::class . ':hello');
    $app->get('/jsondata', HomeController::class . ':jsondata');

    //Routes for Errors Pages
    $app->group('/errors', function (RouteCollectorProxy $group) {
    // ??: Couldn't use /error url. Is it reserved??
        $group->get('/forbidden', ErrorController::class . ':forbidden');
        $group->get('/internal', ErrorController::class . ':internal');
    });

};