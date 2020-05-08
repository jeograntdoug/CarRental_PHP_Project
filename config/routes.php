<?php
    declare(strict_types=1);

    namespace App\Controller;

    use DB;
    use Slim\App;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Routing\RouteCollectorProxy;

    return function (App $app) {
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

        $app->post('/search/location/', function (Request $request, Response $response, array $args) {
            $response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
            $searchText = $request->getBody();

            $result = DB::query("SELECT * FROM cities WHERE postalCode like %ss", $searchText);
            $response->getBody()->write($result);

            return $response;
        });
    };