<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use DB;

class UserReservationController
{
    public function reviewReservation(Request $request, Response $response) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'review_reserve.html.twig');
    }

    public function chooseCar (Request $request, Response $response, array $args) 
    {
        $view = Twig::fromRequest($request);
        $get = $request->getQueryParams();

        if(!isset($get['pickupStoreId'])){
            return $response->withHeader("Location","/");
        }

        $vehicleList = DB::query(
            "SELECT ct.*
            FROM cars AS c
            LEFT JOIN carTypes AS ct
            ON ct.id = c.carTypeid
            WHERE c.storeId = %s
            AND c.status = 'avaliable'
            GROUP BY ct.id", $get['pickupStoreId']
        );

        $categoryPriceList = $this->getMinPrice("category");
        $psgNumPriceList = $this->getMinPrice("passengers");
        $bagNumPriceList = $this->getMinPrice("bags");

        return $view->render($response, 'car_selection.html.twig', [
            'vehicleList' => $vehicleList,
            'categoryPriceList' => $categoryPriceList,
            'psgNumPriceList' => $psgNumPriceList,
            'bagNumPriceList' => $bagNumPriceList
        ]);
    }

    private function getMinPrice($column)
    {
        $column = "ct." . $column;

        $minPriceList = DB::query(
            "SELECT %l, min(ct.dailyPrice) AS 'dailyPrice'
            FROM cars AS c
            LEFT JOIN carTypes AS ct
            ON ct.id = c.carTypeid
            WHERE c.storeId = 3
            AND c.status = 'avaliable'
            GROUP BY %l", $column, $column
        );
        return $minPriceList;
    }
}