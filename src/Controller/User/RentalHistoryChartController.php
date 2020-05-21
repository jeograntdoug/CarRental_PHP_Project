<?php

namespace App\Controller;

use App\UserValidator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use Slim\Views\Twig;
use DB;



class RentalHistoryChartController
{
    public function index (Request $request, Response $response, array $args) {
        $view = Twig::fromRequest($request);
        if (isset($_SESSION['userId'])) {
            $userId = $_SESSION['userId'];
            $orders = DB::query("SELECT * FROM orders WHERE userId = %s", $userId);
            $rawData = DB::query("SELECT monthname(createdTS) as 'Month', 
                                    year(createdTS) as 'Year',
                                    SUM(totalPrice) as 'Exp'                                        
                                    FROM orders 
                                    WHERE userId=%s
                                    GROUP BY month(createdTS),
                                                year(createdTS)
                                    ORDER BY createdTS", $userId);

            $dataPoints = [];
            foreach ($rawData as $point) {
                array_push($dataPoints, array('y' => $point['Exp'], 'label' => $point['Month'] . ',' . $point['Year']));
            }

            return $view->render($response, 'summary_orders.html.twig', [
                'orders' => $orders,
                'keyList' => [
                    'id', 'reservationId', 'userId', 'carId', 'createdTS', 'returnDateTime', 'totalPrice', 'rentStoreId', 'returnStoreId',
                ],
                'dataPoints' => json_encode($dataPoints, JSON_NUMERIC_CHECK)
            ]);
        } else {
            return $view->render($response, 'login.html.twig', []);
        }
    }

    public function showOrderHistoryMap (Request $request, Response $response, array $args) {
            $view = Twig::fromRequest($request);
            if (isset($_SESSION['userId'])) {
                $userId = $_SESSION['userId'];
                // $orders = DB::query("SELECT * FROM orders WHERE userId = %s", $userId);
                $monthlyMileage = DB::query("SELECT monthname(createdTS) as 'Month', 
                                        year(createdTS) as 'Year',                                        
                                        SUM(returnMileage-startMileage) as 'Mileage'
                                        FROM orders 
                                        WHERE userId=%s
                                        GROUP BY month(createdTS),
                                                 year(createdTS)                                                                                              
                                        ORDER BY createdTS", $userId);


                return $view->render($response, 'summary_map.html.twig', [
                    // 'orders' => $orders,
                    'keyList' => [
                        'Year', 'Month', 'Mileage'
                    ],
                    'monthlyMileage' => $monthlyMileage
                ]);
            } else {
                return $view->render($response, 'login.html.twig', []);
            }
        }
}