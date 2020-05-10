<?php
    declare(strict_types=1);

    namespace App\Controller;

use App\Middleware\AuthMiddleware;
use DB;
    use Slim\App;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
    use Slim\Routing\RouteCollectorProxy;
    use Slim\Views\Twig;


    return function (App $app) {
        // Routes

        $app->get('/', HomeController::class . ':home');
        $app->get('/ajax/logout', HomeController::class . ':logout');

        $app->group('', function (RouteCollectorProxy $group) {
            $group->get('/register', HomeController::class . ':register');
            $group->get('/login', HomeController::class . ':login');
            $group->post('/login', AuthController::class . ':authorize');
            $group->post('/register', UserController::class . ':create');

        })->add(AuthMiddleware::mustNotLogin());


        //Routes for Errors Pages
        $app->group('/errors', function (RouteCollectorProxy $group) {
            // ??: Couldn't use /error url. Is it reserved??
            $group->get('/forbidden', ErrorController::class . ':forbidden');
            $group->get('/pagenotfound', ErrorController::class . ':pageNotFound');
            $group->get('/internal', ErrorController::class . ':internal');
        });

        $app->post('/search/location', function (Request $request, Response $response, array $args) {
            $response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');

            $jsonText = $request->getBody()->getContents();
            
            $data = json_decode($jsonText,true);

            $searchText = $data['searchText'];

            $secondChar = substr($searchText, 1,1);
            if(ctype_digit($secondChar) && strlen($searchText) <=3) {
                $result = DB::query("SELECT * FROM cities WHERE postalCode like %s", $searchText . '%');
            }else{
                $result = DB::query("SELECT * FROM cities WHERE name like %s", $searchText . '%');
            }

            $response->getBody()->write(json_encode($result));

            return $response;
        });

        $app->post('/car_selection', function (Request $request, Response $response, array $args){
            $view = Twig::fromRequest($request);
            //$response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
            $allVehicles = DB::query("SELECT * FROM cartypes");

            return $view->render($response, 'car_selection.html.twig',[
                'allVehicles'=>$allVehicles
            ]);
        });

        $app->get('/review_reserve', function (Request $request, Response $response, array $args){
            $view = Twig::fromRequest($request);
            //$response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');


            return $view->render($response, 'review_reserve.html.twig',[

            ]);
        });
    };