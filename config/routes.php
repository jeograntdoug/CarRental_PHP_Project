<?php /** @noinspection ALL */
    declare(strict_types=1);

    namespace App\Controller;

    use App\Middleware\AuthMiddleware;
    use DB;
    use Respect\Validation\Rules\Number;
    use Slim\App;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Routing\RouteCollectorProxy;
    use Slim\Views\Twig;


    return function (App $app) {
        // Routes

        $app->get('/', HomeController::class . ':home');

        $app->group('', function (RouteCollectorProxy $group) {
            $group->get('/ajax/logout', HomeController::class . ':logout');
            $group->get('/profile/{id:[0-9]+}', UserController::class . ':show');
            $group->post('/profile/{id:[0-9]+}', UserController::class . ':update');
        })->add(AuthMiddleware::mustBeLogin());


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

            $group->get('/ajax/stores', AdminStoreController::class . ':index');
            $group->post('/ajax/stores', AdminStoreController::class . ':create');
            $group->patch('/ajax/stores/{id:[0-9]+}', AdminStoreController::class . ':edit');
            $group->delete('/ajax/stores/{id:[0-9]+}', AdminStoreController::class . ':delete');
        })->add(AuthMiddleware::mustBeLoginAsAdmin());

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
                $result = DB::query("SELECT * FROM cities WHERE postalCode like %s ORDER BY name", $searchText . '%');
            } else {
                $result = DB::query("SELECT * FROM cities WHERE name like %s ORDER BY name", $searchText . '%');
            }

            $response->getBody()->write(json_encode($result));

            return $response;
        });

        $app->post('/car_selection', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);
            $dateLocateData = $request->getParsedBody();

            $_SESSION['pickupLocation'] = $dateLocateData['pickupLocation'];
            $_SESSION['isDiffLocation'] = isset($dateLocateData['isDiffLocation']);
            $_SESSION['returnLocation'] = $dateLocateData['returnLocation'];
            $_SESSION['pickupDate'] = $dateLocateData['pickupDate'];
            $_SESSION['pickupTime'] = $dateLocateData['pickupTime'];
            $_SESSION['returnDate'] = $dateLocateData['returnDate'];
            $_SESSION['returnTime'] = $dateLocateData['returnTime'];

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
                'pass2' => $pass2,
                'pass4' => $pass4,
                'pass5' => $pass5,
                'pass7' => $pass7,
                'bag3' => $bag3,
                'bag4' => $bag4,
                'bag5' => $bag5,
                'bag7' => $bag7
            ]);
        });


        $app->post('/review_reserve/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);
            $selId = $args['id'];

            $selVehicle = DB::query("SELECT * FROM cartypes WHERE id = %s", $selId);
            $userInfo = DB::queryFirstRow("SELECT * FROM users WHERE id= 1");

            $dateLocationData = $_SESSION;

            return $view->render($response, 'review_reserve.html.twig', [
                'selVehicle' => $selVehicle[0],
                'userInfo' => $userInfo,
                'dateLocationData' => $dateLocationData
            ]);
        });

        $app->post('/reserve_submit', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);


            return $view->render($response, 'reserve_success.html.twig', [

            ]);
        });


        $app->post('/map/location/{scale:[0-9]+}', function (Request $request, Response $response, array $args) {
            // $view = Twig::fromRequest($request);
            $response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');

            $yourLocation = json_decode($request->getBody()->getContents(), true);

            $scale = isset($args['scale']) == false ? 100 : $args['scale'];

            $allStores = DB::query("SELECT * FROM stores");

            $adjacentStores = array();

            $lat = $yourLocation['lat'];

            foreach ($allStores as $store) {
                $distance = calDistance($yourLocation['lat'], $yourLocation['lng'],
                    floatval($store['latitude']), floatval($store['longitude']), 'K');
                if ($distance <= $scale) {
                    array_push($adjacentStores, $store);
                }
            }

            $response->getBody()->write(json_encode($adjacentStores));
            return $response;
        });

        $app->post('/search/cityname', function (Request $request, Response $response, array $args) {
            // $view = Twig::fromRequest($request);
            $response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');

            $cityname = $request->getBody()->getContents();

            $cityCoorinates = DB::queryFirstRow("SELECT latitude as 'lat', longitude as 'lng' FROM cities WHERE name = %s", json_decode($cityname));


            $response->getBody()->write(json_encode($cityCoorinates));
            return $response;
        });


    };

    function calDistance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        $accuracy = 0.0001;
        if ((abs($lat1 - $lat2) < $accuracy) && (abs($lon1 == $lon2) < $accuracy)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }
