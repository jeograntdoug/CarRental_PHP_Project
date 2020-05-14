<?php

namespace App\Controller;

use App\Validator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use DB;

class AdminCarController
{
    public function index (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        $carList = DB::query(
            "SELECT id, carTypeId, model, year, manufacturer, milleage, status, dailyPrice, storeId, description, fuelType
            FROM cars"
             );

        return $view->render($response, 'admin/item_list.html.twig',[
            'itemTitle' => 'Car',
            'itemList' => $carList,
            'itemKeyList' =>  ['id', 'carTypeId', 'model', 'year', 'manufacturer', 'milleage', 'status', 'dailyPrice', 'storeId', 'description', 'fuelType']
        ]);
    }

    public function showAll (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        $carList = DB::query(
            "SELECT id, carTypeId, model, year, manufacturer, milleage, status, dailyPrice, storeId, description, fuelType
            FROM cars"
             );

        return $view->render($response, 'admin/cards/item_card.html.twig', [
            'itemList' => $carList
        ]);
    }

    //WIP
    public function create (Request $request, Response $response, array $args)
    {
        $jsonData = json_decode($request->getBody(), true);

        $errorList = Validator::car($jsonData);

        if(empty($errorList)){
            DB::insert('cars',$jsonData);
        }

        $response->getBody()->write(json_encode([
            'errorList' => $errorList
        ]));

        return $response;
    }

    public function delete (Request $request, Response $response, array $args)
    {
        $carId = $args['id'];

        DB::delete('cars','id=%i', $carId);
        $response->getBody()->write(json_encode(DB::affectedRows()));

        return $response;
    }

    public function edit (Request $request, Response $response, array $args)
    {
        $fieldList = ['id', 'carTypeId', 'model', 'year', 'manufacturer', 'milleage', 'status', 'dailyPrice', 'storeId', 'description'];
        $carId = $args['id'];

        $jsonData = json_decode($request->getBody(), true);

        $data = [
            $fieldList[$jsonData['fieldIndex']] => $jsonData['value']
        ];

        $errorList = Validator::car($jsonData, false);

        if(empty($errorList)){
            DB::update('cars',$data,'id=%s',$carId);
        }

        $response->getBody()->write(json_encode([
            'errorList' => $errorList
        ]));

        return $response;
    }
}