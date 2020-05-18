<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use App\Validator;
use DB;

class AdminReservationController extends AdminController 
{
    public function __construct(){
        $this->itemTitle = 'Reservation';

        $this->tableName = 'reservations';

        $this->fieldList = [
            'id', 'userId', 'carTypeId', 'startDateTime', 'returnDateTime',
            'dailyPrice', 'netFees', 'rentStoreId', 'returnStoreId'
        ];

        $this->fieldListInHeader = [
            'ID', 'USER', 'CAR TYPE', 'START DATE', 'RETURN DATE',
            'PRICE PER DAY', 'NET FEE', 'RENT STORE', 'RETURN STORE'
        ];
    }


    public function showAll (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        $get = $this->parseGetRequest($request);

        $startResv = ($get['currentPage'] - 1) * $this->records_per_page;

        $resvList = DB::query(
            "SELECT r.id, r.userId, r.carTypeId, r.startDateTime, r.returnDateTime, r.dailyPrice, r.netFees, r.rentStoreId, r.returnStoreId , o.id AS 'orderId'
            FROM reservations AS r
            LEFT JOIN orders AS o
            ON o.reservationId = r.id
            ORDER BY %l %l
            LIMIT %i, %i",
            $get['sortBy'], $get['order'], 
            $startResv, $this->records_per_page
        );

        return $view->render($response, 'admin/cards/' . $this->itemTemplate, [
            'itemTitle' => $this->itemTitle,
            'itemList' => $resvList,
            'currentPage' => $get['currentPage'],
            'totalPage' => $get['totalPage']
        ]);
    }
}