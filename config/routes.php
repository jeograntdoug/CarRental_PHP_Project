<?php /** @noinspection ALL */
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
        $app->get('/profile/{id:[0-9]+}', UserController::class . ':show');
        $app->post('/profile/{id:[0-9]+}', UserController::class . ':update');


        $app->group('', function (RouteCollectorProxy $group) {
            $group->get('/register', HomeController::class . ':register');
            $group->get('/login', HomeController::class . ':login');
            $group->post('/login', AuthController::class . ':authorize');
            $group->post('/register', UserController::class . ':create');

        })->add(AuthMiddleware::mustNotLogin());


        $app->group('/admin', function (RouteCollectorProxy $group) {
            $group->get('', AdminController::class . ':home');
            $group->get('/stores', AdminController::class . ':storeList');
            $group->get('/cartypes', AdminController::class . ':carTypeList');
            $group->get('/cars', AdminController::class . ':carList');
            $group->get('/reservations', AdminController::class . ':reservationList');
        });

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

            $data = json_decode($jsonText, true);

            $searchText = $data['searchText'];

            $secondChar = substr($searchText, 1, 1);
            if (ctype_digit($secondChar) && strlen($searchText) <= 3) {
                $result = DB::query("SELECT * FROM cities WHERE postalCode like %s", $searchText . '%');
            } else {
                $result = DB::query("SELECT * FROM cities WHERE name like %s", $searchText . '%');
            }

            $response->getBody()->write(json_encode($result));

            return $response;
        });

        $app->post('/car_selection', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);

            $allVehicles = DB::query("SELECT * FROM cartypes");
            $carMinPrice = DB::query("SELECT MIN(dailyPrice) as 'min' from cartypes WHERE category = %s", "Car")[0]['min'];
            $suvMinPrice = DB::query("SELECT MIN(dailyPrice) as 'min' from cartypes WHERE category = %s", "SUV")[0]['min'];
            $vanMinPrice = DB::query("SELECT MIN(dailyPrice)  as 'min' from cartypes WHERE category = %s", "Van")[0]['min'];
            $truckMinPrice = DB::query("SELECT MIN(dailyPrice)  as 'min' from cartypes WHERE category = %s", "Truck")[0]['min'];

            $pass2 = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE passengers >= 2")[0]['min'];
            $pass4 = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE passengers >= 4")[0]['min'];
            $pass5 = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE passengers >= 5")[0]['min'];
            $pass7 = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE passengers >= 7")[0]['min'];

            $bag3 = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE bags >= 3")[0]['min'];
            $bag4 = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE bags >= 4")[0]['min'];
            $bag5 = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE bags >= 5")[0]['min'];
            $bag7 = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE bags >= 7")[0]['min'];


            return $view->render($response, 'car_selection.html.twig', [
                'allVehicles' => $allVehicles,
                'carMinPrice' => $carMinPrice,
                'suvMinPrice' => $suvMinPrice,
                'vanMinPrice' => $vanMinPrice,
                'truckMinPrice' => $truckMinPrice,
                'pass2'=>$pass2,
                'pass4'=>$pass4,
                'pass5'=>$pass5,
                'pass7'=>$pass7,
                'bag3'=>$bag3,
                'bag4'=>$bag4,
                'bag5'=>$bag5,
                'bag7'=>$bag7
            ]);
        });


        $app->post('/review_reserve/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);
            $selId = $args['id'];

            $selVehicle = DB::query("SELECT * FROM cartypes WHERE id = %s", $selId);

            return $view->render($response, 'review_reserve.html.twig', [
                'selVehicle' => $selVehicle[0]
            ]);
        });


        /*     $app->post('/review_reserve', function (Request $request, Response $response, array $args){
                 $view = Twig::fromRequest($request);

             $jsonText = $request->getBody()->getContents();

                // $data = json_decode($jsonText,true);

                 $result = DB::queryFirstRow("SELECT * FROM cartypes WHERE id = 2" );
                 $jsonData = json_encode($result);
                 return $response->getBody()->write($jsonData);
             });*/


    };