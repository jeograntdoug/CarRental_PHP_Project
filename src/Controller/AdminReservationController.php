<?php

namespace App\Controller;

use App\Validator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use DB;

class AdminReservationController
{
    public function index (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        $resvList = DB::query(
            "SELECT id, userId, carTypeId, startDateTime, returnDateTime, dailyPrice, netFees, rentStoreId, returnStoreId
            FROM reservations LIMIT 100"
             );

        return $view->render($response, 'admin/item_list.html.twig',[
            'itemTitle' => 'Reservation',
            'itemList' => $resvList,
            'itemKeyList' => [
                'id', 'userId', 'carTypeId', 'startDateTime', 'returnDateTime',
                'dailyPrice', 'netFees', 'rentStoreId', 'returnStoreId'
            ] 
        ]);
    }

    public function showAll (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        $resvList = DB::query(
            "SELECT id, userId, carTypeId, startDateTime, returnDateTime, dailyPrice, netFees, rentStoreId, returnStoreId
            FROM reservations LIMIT 100"
             );

        return $view->render($response, 'admin/cards/item_card.html.twig', [
            'itemList' => $resvList
        ]);
    }

    public function create (Request $request, Response $response, array $args)
    {
        $jsonData = json_decode($request->getBody(), true);

        // TODO : validation
        // $errorList = Validator::reservation($jsonData);
        $errorList = [];

        if(empty($errorList)){
            DB::insert('reservations',$jsonData);
        }

        $response->getBody()->write(json_encode([
            'errorList' => $errorList
        ]));

        return $response;
    }

    public function delete (Request $request, Response $response, array $args)
    {
        $reservationId = $args['id'];

        DB::delete('reservations','id=%i', $reservationId);
        $response->getBody()->write(json_encode(DB::affectedRows()));

        return $response;
    }

    public function edit (Request $request, Response $response, array $args)
    {
        $fieldList = [
                'id', 'userId', 'carTypeId', 'startDateTime', 'returnDateTime',
                'dailyPrice', 'netFees', 'rentStoreId', 'returnStoreId'
        ];
        $reservationId = $args['id'];

        $jsonData = json_decode($request->getBody(), true);

        $data = [
            $fieldList[$jsonData['fieldIndex']] => $jsonData['value']
        ];

        // TODO : validation
        // $errorList = Validator::reservation($jsonData, false);
        $errorList = [];

        if(empty($errorList)){
            DB::update('reservations',$data,'id=%s',$reservationId);
        }

        $response->getBody()->write(json_encode([
            'errorList' => $errorList
        ]));

        return $response;
    }
}