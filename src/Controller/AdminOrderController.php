<?php

namespace App\Controller;

use App\Validator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use DB;

class AdminOrderController
{
    public function index (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        $orderList = DB::query(
            "SELECT id, reservationId, userId, carId, createdTS, returnDateTime, totalPrice, rentStoreId, returnStoreId
            FROM orders LIMIT 100"
             );

        return $view->render($response, 'admin/item_list.html.twig',[
            'itemTitle' => 'Order',
            'itemList' => $orderList,
            'itemKeyList' => [
                'id', 'reservationId', 'userId', 'carId', 'createdTS', 'returnDateTime', 'totalPrice', 'rentStoreId', 'returnStoreId', 
            ] 
        ]);
    }

    public function showAll (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        $orderList = DB::query(
            "SELECT id, reservationId, userId, carId, createdTS, returnDateTime, totalPrice, rentStoreId, returnStoreId
            FROM orders LIMIT 100"
             );

        return $view->render($response, 'admin/cards/item_card.html.twig', [
            'itemList' => $orderList
        ]);
    }

    public function create (Request $request, Response $response, array $args)
    {
        $jsonData = json_decode($request->getBody(), true);

        // TODO : validation
        // $errorList = Validator::order($jsonData);
        $errorList = [];

        if(empty($errorList)){
            DB::insert('orders',$jsonData);
        }

        $response->getBody()->write(json_encode([
            'errorList' => $errorList
        ]));

        return $response;
    }

    public function delete (Request $request, Response $response, array $args)
    {
        $orderId = $args['id'];

        DB::delete('orders','id=%i', $orderId);
        $response->getBody()->write(json_encode(DB::affectedRows()));

        return $response;
    }

    public function edit (Request $request, Response $response, array $args)
    {
        $fieldList = [
            'id', 'reservationId', 'userId', 'carId', 'createdTS', 'returnDateTime', 'totalPrice', 'rentStoreId', 'returnStoreId', 
        ];

        $orderId = $args['id'];

        $jsonData = json_decode($request->getBody(), true);

        $data = [
            $fieldList[$jsonData['fieldIndex']] => $jsonData['value']
        ];

        // TODO : validation
        // $errorList = Validator::reservation($jsonData, false);
        $errorList = [];

        if(empty($errorList)){
            DB::update('orders',$data,'id=%s',$orderId);
        }

        $response->getBody()->write(json_encode([
            'errorList' => $errorList
        ]));

        return $response;
    }
}