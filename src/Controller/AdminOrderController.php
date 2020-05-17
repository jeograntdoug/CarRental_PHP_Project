<?php

namespace App\Controller;

use App\Validator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use DB;

class AdminOrderController extends AdminController
{
    public function __construct(){
        $this->itemTitle = 'Order';

        $this->tableName = 'orders';

        $this->fieldList = [
            'id', 'reservationId', 'userId', 'carId', 'createdTS', 
            'returnDateTime', 'totalPrice', 'rentStoreId', 'returnStoreId',
        ];

        $this->fieldListInHeader = [
            'ID', 'RESV ID', 'USER', 'CAR', 'START DATE', 
            'RETURN DATE', 'TOTAL PRICE', 'RENT STORE', 'RETURN STORE'
        ];
    }

    public function create (Request $request, Response $response, array $args)
    {
        $jsonData = json_decode($request->getBody(), true);

        $errorList = [];
        if(isset($jsonData['reservationId'])){
            $resv = DB::queryFirstRow(
                'SELECT * FROM reservations 
                WHERE id=%s', 
                $jsonData['reservationId']
            );

            if(empty($resv)){
                $errorList['reservationId'] = "Invalid reservation id";
            } else {
                $car = DB::queryFirstRow(
                    "SELECT id, mileage 
                    FROM cars
                    WHERE storeId = %s
                    AND status = 'avaliable'", 
                    $resv['rentStoreId']
                );

                if(empty($car)){
                    $errorList['car'] = "There is no avaliable Car in this store";
                } else {
                    $jsonData = [
                        'reservationId' => $resv['id'],
                        'userId' => $resv['userId'],
                        'carId' => $car['id'],
                        'startMileage' => $car['mileage'],
                        'rentStoreId' => $resv['rentStoreId'],
                    ];
                }
            }
        } else{
            if($this->validator != null){
                $errorList = call_user_func($this->validator,$jsonData);
            }
        }

        if(empty($errorList)){
            // Transaction
            DB::insert($this->tableName,$jsonData);
            $response->getBody()->write(json_encode(DB::insertId()));
            DB::update("cars", ['status' => 'renting'], "id=%s", $car['id']);

            return $response;
        } 
        else 
        {
            $response->getBody()->write(json_encode([
                'errorList' => $errorList
            ]));
            return $response->withStatus(400);
        }

    }


    public function showAll (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        $get = $this->parseGetRequest($request);

        $startResv = ($get['currentPage'] - 1) * $this->records_per_page;

        if($get['sortBy'] == 'returnDateTime')
        {
            $get['sortBy'] = '-returnDateTime';
            $get['order'] = $get['order'] == 'ASC' ? 'DESC' : 'ASC';
        }

        $resvList = DB::query(
            "SELECT %l FROM %l
            ORDER BY %l %l
            LIMIT %i, %i",
            implode(",",$this->fieldList),
            $this->tableName,
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