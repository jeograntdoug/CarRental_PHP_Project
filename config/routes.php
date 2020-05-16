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

            $group->get('/stores', AdminStoreController::class . ':index');
            $group->get('/ajax/stores', AdminStoreController::class . ':showAll');
            $group->post('/ajax/stores', AdminStoreController::class . ':create');
            $group->patch('/ajax/stores/{id:[0-9]+}', AdminStoreController::class . ':edit');
            $group->delete('/ajax/stores/{id:[0-9]+}', AdminStoreController::class . ':delete');

            $group->get('/cars', AdminCarController::class . ':index');
            $group->get('/ajax/cars', AdminCarController::class . ':showAll');
            $group->post('/ajax/cars', AdminCarController::class . ':create');
            $group->patch('/ajax/cars/{id:[0-9]+}', AdminCarController::class . ':edit');
            $group->delete('/ajax/cars/{id:[0-9]+}', AdminCarController::class . ':delete');

            $group->get('/cartypes', AdminCarTypeController::class . ':index');
            $group->get('/ajax/cartypes', AdminCarTypeController::class . ':showAll');
            $group->post('/ajax/cartypes', AdminCarTypeController::class . ':create');
            $group->patch('/ajax/cartypes/{id:[0-9]+}', AdminCarTypeController::class . ':edit');
            $group->delete('/ajax/cartypes/{id:[0-9]+}', AdminCarTypeController::class . ':delete');

            $group->get('/reservations', AdminReservationController::class . ':index');
            $group->get('/ajax/reservations', AdminReservationController::class . ':showAll');
            $group->post('/ajax/reservations', AdminReservationController::class . ':create');
            $group->patch('/ajax/reservations/{id:[0-9]+}', AdminReservationController::class . ':edit');
            $group->delete('/ajax/reservations/{id:[0-9]+}', AdminReservationController::class . ':delete');

            $group->get('/orders', AdminOrderController::class . ':index');
            $group->get('/ajax/orders', AdminOrderController::class . ':showAll');
            $group->post('/ajax/orders', AdminOrderController::class . ':create');
            $group->patch('/ajax/orders/{id:[0-9]+}', AdminOrderController::class . ':edit');
            $group->delete('/ajax/orders/{id:[0-9]+}', AdminOrderController::class . ':delete');
        });
        //->add(AuthMiddleware::mustBeLoginAsAdmin());

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

            $_SESSION['isDiffLocation'] = isset($dateLocateData['isDiffLocation']);
            $_SESSION['returnStoreId'] = $dateLocateData['returnStoreId'];
            $_SESSION['pickupDate'] = $dateLocateData['pickupDate'];
            $_SESSION['pickupTime'] = $dateLocateData['pickupTime'];
            $_SESSION['returnDate'] = $dateLocateData['returnDate'];
            $_SESSION['returnTime'] = $dateLocateData['returnTime'];

            $pickupStore = DB::queryFirstRow("SELECT * FROM stores WHERE id=%s", $dateLocateData['pickupStoreId']);
            $_SESSION['pickupStoreId'] = $pickupStore['id'];
            $_SESSION['pickupAddress'] = $pickupStore['address'];
            $_SESSION['pickupStoreName'] = $pickupStore['storeName'];
            $_SESSION['pickupCity'] = $pickupStore['city'];
            $_SESSION['pickupProvince'] = $pickupStore['province'];
            $_SESSION['pickupPostCode'] = $pickupStore['postCode'];

            $allVehicles = DB::query("SELECT * FROM cartypes");
            $_SESSION['carMinPrice'] = DB::query("SELECT MIN(dailyPrice) as 'min' from cartypes WHERE category = %s", "Car")[0]['min'];
            $_SESSION['suvMinPrice'] = DB::query("SELECT MIN(dailyPrice) as 'min' from cartypes WHERE category = %s", "SUV")[0]['min'];
            $_SESSION['vanMinPrice'] = DB::query("SELECT MIN(dailyPrice)  as 'min' from cartypes WHERE category = %s", "Van")[0]['min'];
            $_SESSION['truckMinPrice'] = DB::query("SELECT MIN(dailyPrice)  as 'min' from cartypes WHERE category = %s", "Truck")[0]['min'];

            $_SESSION['pass2'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE passengers >= 2")[0]['min'];
            $_SESSION['pass4'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE passengers >= 4")[0]['min'];
            $_SESSION['pass5'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE passengers >= 5")[0]['min'];
            $_SESSION['pass7'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE passengers >= 7")[0]['min'];

            $_SESSION['bag3'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE bags >= 3")[0]['min'];
            $_SESSION['bag4'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE bags >= 4")[0]['min'];
            $_SESSION['bag5'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE bags >= 5")[0]['min'];
            $_SESSION['bag7'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE bags >= 7")[0]['min'];
            $vehiclesInfo = $_SESSION;

            return $view->render($response, 'car_selection.html.twig', [
                'allVehicles' => $allVehicles,
                'vehiclesInfo' => $vehiclesInfo
            ]);
        });


        $app->post('/review_reserve/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);

            $selId = $args['id'];

            $_SESSION['selVehicleTypeId'] = $selId;
            $selVehicle = DB::queryFirstRow("SELECT * FROM cartypes WHERE id = %s", $selId);
            $_SESSION['selVehicle'] = $selVehicle;

            $userInfo = DB::queryFirstRow("SELECT * FROM users WHERE id= 1");

            return $view->render($response, 'review_reserve.html.twig', [
                'selVehicle' => $selVehicle,
                'userInfo' => $userInfo,
                'dateLocationData' => $_SESSION
            ]);
        });

        $app->post('/modified_datetime', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);

            $selVehicle = $_SESSION['selVehicle'];
            $modifiedDateTime = $request->getParsedBody();
            $_SESSION['pickupDate'] = $modifiedDateTime['pickupDate'];
            $_SESSION['pickupTime'] = $modifiedDateTime['pickupTime'];
            $_SESSION['returnDate'] = $modifiedDateTime['returnDate'];
            $_SESSION['returnTime'] = $modifiedDateTime['returnTime'];

            $userInfo = DB::queryFirstRow("SELECT * FROM users WHERE id= 1");

            return $view->render($response, 'review_reserve.html.twig', [
                'selVehicle' => $selVehicle,
                'userInfo' => $userInfo,
                'dateLocationData' => $_SESSION
            ]);
        });

        $app->post('/modified_locations', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);

            $selVehicle = $_SESSION['selVehicle'];
            $modifiedLocationData = $request->getParsedBody();

            $pickupStore = DB::queryFirstRow("SELECT * FROM stores WHERE id=%s", $modifiedLocationData['pickupStoreId']);
            $_SESSION['pickupStoreId'] = $pickupStore['id'];
            $_SESSION['pickupAddress'] = $pickupStore['address'];
            $_SESSION['pickupStoreName'] = $pickupStore['storeName'];
            $_SESSION['pickupCity'] = $pickupStore['city'];
            $_SESSION['pickupProvince'] = $pickupStore['province'];
            $_SESSION['pickupPostCode'] = $pickupStore['postCode'];

            $userInfo = DB::queryFirstRow("SELECT * FROM users WHERE id= 1");

            return $view->render($response, 'review_reserve.html.twig', [
                'selVehicle' => $selVehicle,
                'userInfo' => $userInfo,
                'dateLocationData' => $_SESSION
            ]);
        });


        $app->post('/reserve_submit', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);

            //$response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');

            $jsonText = $request->getBody()->getContents();

            $reservationData = json_decode($jsonText, true);

            $datetime = $_SESSION['pickupDate'] . " " . $_SESSION['pickupTime'];

            $json = array(
                "userId" => 1,
                "carTypeId" => $_SESSION['selVehicleTypeId'],
                "startDateTime" => date_create_from_format('Y-m-d H:i', $_SESSION['pickupDate'] . " " . $_SESSION['pickupTime']),
                "returnDateTime" => date_create_from_format('Y-m-d H:i', $_SESSION['returnDate'] . " " . $_SESSION['returnTime']),
                "dailyPrice" => $reservationData['dailyPrice'],
                "netFees" => $reservationData['netFees'],
                "tps" => $reservationData['tps'],
                "tvq" => $reservationData['tvq'],
                "rentDays" => $reservationData['rentDays'],
                "rentStoreId" => $_SESSION['pickupStoreId'],
                "returnStoreId" => $_SESSION['pickupStoreId'], //FIXME when return store is implemented!!!
            );

            $result = DB::insert("reservations", $json);

            if ($result) {
                $result = array(
                    "url" => "../"
                );
            }

            $response->getBody()->write(json_encode($result));

            return $response;
        });

        $app->get('/modify_datetime', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);
            return $view->render($response, 'modify_datetime.html.twig', [

            ]);
        });

        $app->get('/modify_locations', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);
            return $view->render($response, 'modify_locations.html.twig', [

            ]);
        });

        $app->get('/modify_car_selection', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);

            $allVehicles = DB::query("SELECT * FROM cartypes");
            $_SESSION['carMinPrice'] = DB::query("SELECT MIN(dailyPrice) as 'min' from cartypes WHERE category = %s", "Car")[0]['min'];
            $_SESSION['suvMinPrice'] = DB::query("SELECT MIN(dailyPrice) as 'min' from cartypes WHERE category = %s", "SUV")[0]['min'];
            $_SESSION['vanMinPrice'] = DB::query("SELECT MIN(dailyPrice)  as 'min' from cartypes WHERE category = %s", "Van")[0]['min'];
            $_SESSION['truckMinPrice'] = DB::query("SELECT MIN(dailyPrice)  as 'min' from cartypes WHERE category = %s", "Truck")[0]['min'];

            $_SESSION['pass2'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE passengers >= 2")[0]['min'];
            $_SESSION['pass4'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE passengers >= 4")[0]['min'];
            $_SESSION['pass5'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE passengers >= 5")[0]['min'];
            $_SESSION['pass7'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE passengers >= 7")[0]['min'];

            $_SESSION['bag3'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE bags >= 3")[0]['min'];
            $_SESSION['bag4'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE bags >= 4")[0]['min'];
            $_SESSION['bag5'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE bags >= 5")[0]['min'];
            $_SESSION['bag7'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM cartypes WHERE bags >= 7")[0]['min'];
            $vehiclesInfo = $_SESSION;

            return $view->render($response, 'car_selection.html.twig', [
                'allVehicles' => $allVehicles,
                'vehiclesInfo' => $vehiclesInfo
            ]);
        });


        $app->post('/map/location/{scale:[0-9]+}', function (Request $request, Response $response, array $args) {
            // $view = Twig::fromRequest($request);
            $response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');

            $yourLocation = json_decode($request->getBody()->getContents(), true);

            $scale = isset($args['scale']) == false ? 100 : $args['scale'];

            $allStores = DB::query("SELECT * FROM stores");

            $adjacentStores = array();

            foreach ($allStores as $store) {
                $distance = calDistance($yourLocation['lat'], $yourLocation['lng'],
                    floatval($store['latitude']), floatval($store['longitude']), 'K');
                if ($distance <= $scale) {
                    $carNum = DB::queryFirstRow("SELECT COUNT(*) as 'carNum' FROM cars as c WHERE storeId=%s", $store['id']);
                    $avaCarNum = DB::queryFirstRow("SELECT COUNT(*) as 'carNum' FROM cars as c WHERE c.status='avaliable' 
                                             AND storeId=%s", $store['id']);
                    $store['carNum'] = $carNum;
                    $store['avaCarNum'] = $avaCarNum;

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
