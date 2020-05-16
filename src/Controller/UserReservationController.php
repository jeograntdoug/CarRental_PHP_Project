<?php

    namespace App\Controller;

    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;
    use Slim\Views\Twig;
    use DB;

    class UserReservationController
    {

        public function selectCarType(Request $request, Response $response)
        {
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

            return $view->render($response, 'user/car_selection.html.twig', [
                'allVehicles' => $allVehicles,
                'vehiclesInfo' => $vehiclesInfo
            ]);
        }
    }