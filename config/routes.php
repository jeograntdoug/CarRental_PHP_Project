<?php /** @noinspection ALL */
    declare(strict_types=1);

    namespace App\Controller;

    use App\Middleware\AuthMiddleware;
    use DB;
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
            $group->get('/users/{id:[0-9]+}', UserController::class . ':show');
            $group->post('/users/{id:[0-9]+}', UserController::class . ':update');
        })->add(AuthMiddleware::mustBeLogin());


        $app->group('', function (RouteCollectorProxy $group) {
            $group->get('/register', HomeController::class . ':register');
            $group->get('/login', HomeController::class . ':login');
            $group->post('/login', AuthController::class . ':authorize');
            $group->post('/register', UserController::class . ':create');

        })->add(AuthMiddleware::mustNotLogin());


        $app->group('/admin', function (RouteCollectorProxy $group) {
            // $group->get('', AdminController::class . ':home');
            $group->redirect('', '/admin/stores');

            $group->get('/ajax/users/{id:[0-9]+}', UserController::class . ':showToAdmin');

            $group->get('/stores', AdminStoreController::class . ':index');
            $group->get('/ajax/stores', AdminStoreController::class . ':showAll');
            $group->post('/ajax/stores', AdminStoreController::class . ':create');
            $group->get('/ajax/stores/{id:[0-9]+}', AdminStoreController::class . ':show');
            $group->patch('/ajax/stores/{id:[0-9]+}', AdminStoreController::class . ':edit');
            $group->delete('/ajax/stores/{id:[0-9]+}', AdminStoreController::class . ':delete');

            $group->get('/cars', AdminCarController::class . ':index');
            $group->get('/ajax/cars', AdminCarController::class . ':showAll');
            $group->post('/ajax/cars', AdminCarController::class . ':create');
            $group->get('/ajax/cars/{id:[0-9]+}', AdminCarController::class . ':show');
            $group->patch('/ajax/cars/{id:[0-9]+}', AdminCarController::class . ':edit');
            $group->delete('/ajax/cars/{id:[0-9]+}', AdminCarController::class . ':delete');

            $group->get('/cartypes', AdminCarTypeController::class . ':index');
            $group->get('/ajax/cartypes', AdminCarTypeController::class . ':showAll');
            $group->post('/ajax/cartypes', AdminCarTypeController::class . ':create');
            $group->get('/ajax/cartypes/{id:[0-9]+}', AdminCarTypeController::class . ':show');
            $group->patch('/ajax/cartypes/{id:[0-9]+}', AdminCarTypeController::class . ':edit');
            $group->delete('/ajax/cartypes/{id:[0-9]+}', AdminCarTypeController::class . ':delete');

            $group->get('/reservations', AdminReservationController::class . ':index');
            $group->get('/ajax/reservations', AdminReservationController::class . ':showAll');
            $group->post('/ajax/reservations', AdminReservationController::class . ':create');
            $group->get('/ajax/reservations/{id:[0-9]+}', AdminReservationController::class . ':show');
            $group->patch('/ajax/reservations/{id:[0-9]+}', AdminReservationController::class . ':edit');
            $group->delete('/ajax/reservations/{id:[0-9]+}', AdminReservationController::class . ':delete');

            $group->get('/orders', AdminOrderController::class . ':index');
            $group->get('/ajax/orders', AdminOrderController::class . ':showAll');
            $group->post('/ajax/orders', AdminOrderController::class . ':create');
            $group->get('/ajax/orders/{id:[0-9]+}', AdminOrderController::class . ':show');
            $group->patch('/ajax/orders/{id:[0-9]+}', AdminOrderController::class . ':edit');
            $group->delete('/ajax/orders/{id:[0-9]+}', AdminOrderController::class . ':delete');
        })->add(AuthMiddleware::mustBeLoginAsAdmin());

        //Routes for Errors Pages
        $app->group('/errors', function (RouteCollectorProxy $group) {
            // ??: Couldn't use /error url. Is it reserved??
            $group->get('/forbidden', ErrorController::class . ':forbidden');
            $group->get('/pagenotfound', ErrorController::class . ':pageNotFound');
            $group->get('/internal', ErrorController::class . ':internal');
        });

        // User profile
        $app->get('/summary/orders', RentalHistoryChartController::class . ':index');
        $app->get('/summary/map', RentalHistoryMapController::class . ':index');
        $app->post('/summary/map', RentalHistoryMapController::class . ':show');


        // User reservation
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

        $app->get('/car_selection', UserReservationController::class . ':showAvaliableCar');
        $app->post('/car_selection', UserReservationController::class . ':chooseCar' );
        $app->get('/review_reserve', UserReservationController::class . ':reviewReservation');
        $app->get('/ajax/review_reserve/store/{id:[0-9]+}', AdminStoreController::class . ':showJson');
        $app->get('/ajax/review_reserve/cartype/{id:[0-9]+}', AdminCarTypeController::class . ':showJson');
        $app->post('/reserve_submit', AdminReservationController::class . ":create");


/*
        $app->post('/reserve_submit', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);

            //$response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');

            $jsonText = $request->getBody()->getContents();

            $reservationData = json_decode($jsonText, true);

            $datetime = $_SESSION['pickupDate'] . " " . $_SESSION['pickupTime'];

            $json = array(
                "userId" => $_SESSION['userId'],
                "carTypeId" => $_SESSION['selVehicleTypeId'],
                "startDateTime" => date_create_from_format('Y-m-d H:i', $_SESSION['pickupDate'] . " " . $_SESSION['pickupTime']),
                "returnDateTime" => date_create_from_format('Y-m-d H:i', $_SESSION['returnDate'] . " " . $_SESSION['returnTime']),
                "dailyPrice" => $reservationData['dailyPrice'],
                "netFees" => $reservationData['netFees'],
                "tps" => $reservationData['tps'],
                "tvq" => $reservationData['tvq'],
                "rentDays" => $reservationData['rentDays'],
                "rentStoreId" => $_SESSION['pickupStoreId'],
                "returnStoreId" => isset($_SESSION['returnStoreId']) ? $_SESSION['returnStoreId'] : $_SESSION['pickupStoreId'], //FIXME when return store is implemented!!!
            );

            $result = DB::insert("reservations", $json);

            if ($result) {
                $result = array(
                    "url" => "../summary/profile"
                );
            }

            $response->getBody()->write(json_encode($result));

            return $response;
        });
*/
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

            $_SESSION['isDiffLocation'] = isset($modifiedLocationData['isDiffLocation']);
            if ($_SESSION['isDiffLocation']) {
                $returnStore = DB::queryFirstRow("SELECT * FROM stores WHERE id=%s", $modifiedLocationData['returnStoreId']);
                $_SESSION['returnStoreId'] = $modifiedLocationData['returnStoreId'];
                $_SESSION['returnAddress'] = $returnStore['address'];
                $_SESSION['returnStoreName'] = $returnStore['storeName'];
                $_SESSION['returnCity'] = $returnStore['city'];
                $_SESSION['returnProvince'] = $returnStore['province'];
                $_SESSION['returnPostCode'] = $returnStore['postCode'];
            } else {
                unset($_SESSION['returnStoreId']);
                unset($_SESSION['returnAddress']);
                unset($_SESSION['returnStoreName']);
                unset($_SESSION['returnCity']);
                unset($_SESSION['returnProvince']);
                unset($_SESSION['returnPostCode']);
            }

            $userInfo = DB::queryFirstRow("SELECT * FROM users WHERE id= 1");

            return $view->render($response, 'review_reserve.html.twig', [
                'selVehicle' => $selVehicle,
                'userInfo' => $userInfo,
                'dateLocationData' => $_SESSION
            ]);
        });

        $app->get('/modify_datetime', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);
            return $view->render($response, 'modify_datetime.html.twig', [

            ]);
        });

        $app->get('/modify_car_selection', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);

            $allVehicles = DB::query("SELECT ct.* FROM carTypes ct, cars cs WHERE cs.storeId= %s 
                                        AND cs.carTypeId=ct.id AND cs.status='avaliable' 
                                        GROUP BY ct.id",
                $_SESSION['pickupStoreId']);
            /*$_SESSION['carMinPrice'] = DB::query("SELECT MIN(dailyPrice) as 'min' from carTypes WHERE category = %s", "Car")[0]['min'];
            $_SESSION['suvMinPrice'] = DB::query("SELECT MIN(dailyPrice) as 'min' from carTypes WHERE category = %s", "SUV")[0]['min'];
            $_SESSION['vanMinPrice'] = DB::query("SELECT MIN(dailyPrice)  as 'min' from carTypes WHERE category = %s", "Van")[0]['min'];
            $_SESSION['truckMinPrice'] = DB::query("SELECT MIN(dailyPrice)  as 'min' from carTypes WHERE category = %s", "Truck")[0]['min'];

            $_SESSION['pass2'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM carTypes WHERE passengers >= 2")[0]['min'];
            $_SESSION['pass4'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM carTypes WHERE passengers >= 4")[0]['min'];
            $_SESSION['pass5'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM carTypes WHERE passengers >= 5")[0]['min'];
            $_SESSION['pass7'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM carTypes WHERE passengers >= 7")[0]['min'];

            $_SESSION['bag3'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM carTypes WHERE bags >= 3")[0]['min'];
            $_SESSION['bag4'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM carTypes WHERE bags >= 4")[0]['min'];
            $_SESSION['bag5'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM carTypes WHERE bags >= 5")[0]['min'];
            $_SESSION['bag7'] = DB::query("SELECT MIN(dailyPrice) as 'min' FROM carTypes WHERE bags >= 7")[0]['min'];*/
            $vehiclesInfo = $_SESSION;

            return $view->render($response, 'car_selection.html.twig', [
                'allVehicles' => $allVehicles,
                'vehiclesInfo' => $vehiclesInfo
            ]);
        });

        $app->get('/modify_locations', function (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);
            return $view->render($response, 'modify_locations.html.twig', [

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
