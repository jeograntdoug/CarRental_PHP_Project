<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use DB;


class RentalHistoryMapController
{
    public function index (Request $request, Response $response, array $args) 
    {
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

    public function show (Request $request, Response $response, array $args) 
    {
        $response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
        $selPeriod = json_decode($request->getBody()->getContents(), true);
        $userId = $_SESSION['userId'];
        $orders = DB::query("SELECT id, createdTS FROM orders WHERE year(createdTS) = %s AND monthname(createdTS)=%s AND userId=%s",
            $selPeriod['year'], $selPeriod['month'], $userId);
        $result = [];
        foreach ($orders as $order) {
            $origin = DB::queryFirstRow("SELECT s.storeName as 'name', s.latitude as 'lat', s.longitude as 'lng' FROM orders o, stores s 
                                            WHERE o.rentStoreId=s.id AND o.id = %s", $order['id']);
            $destination = DB::queryFirstRow("SELECT s.storeName as 'name', s.latitude as 'lat', s.longitude as 'lng' FROM orders o, stores s 
                                            WHERE o.returnStoreId=s.id AND o.id = %s", $order['id']);
            $mileage = DB::queryFirstRow("SELECT (returnMileage-startMileage) as 'mileage' FROM orders WHERE id=%s", $order['id']);
            array_push($result, array('id' => $order['id'], 'origin' => $origin, 'destination' => $destination, 'mileage' => $mileage));
        }

        $response->getBody()->write(json_encode($result, JSON_PRETTY_PRINT));
        return $response;
    }
}