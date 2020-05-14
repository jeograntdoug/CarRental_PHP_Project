<?php

namespace App\Controller;

use App\Validator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use DB;

class AdminCarTypeController
{
    public function index (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        $carTypeList = DB::query(
            "SELECT id, category, subtype, 
                    description, passengers, bags, dailyPrice
            FROM carTypes"
             );

        return $view->render($response, 'admin/item_list.html.twig',[
            'itemTitle' => 'CarType',
            'itemList' => $carTypeList,
            'itemKeyList' => ['id', 'category', 'subtype', 'description', 'passengers', 'bags', 'dailyPrice']
        ]);
    }

    public function showAll (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        $carTypeList = DB::query(
            "SELECT id, category, subtype, 
                    description, passengers, bags, dailyPrice
            FROM carTypes"
             );

        return $view->render($response, 'admin/cards/item_card.html.twig', [
            'itemList' => $carTypeList
        ]);
    }

    //WIP
    public function create (Request $request, Response $response, array $args)
    {
        $jsonData = json_decode($request->getBody(), true);

        $errorList = Validator::carType($jsonData);

        if(empty($errorList)){
            DB::insert('carTypes',$jsonData);
        }

        $response->getBody()->write(json_encode([
            'errorList' => $errorList
        ]));

        return $response;
    }

    public function delete (Request $request, Response $response, array $args)
    {
        $carTypeId = $args['id'];

        DB::delete('carTypes','id=%i', $carTypeId);
        $response->getBody()->write(json_encode(DB::affectedRows()));

        return $response;
    }

    public function edit (Request $request, Response $response, array $args)
    {
        $fieldList = ['id', 'category', 'subtype', 'description', 'passengers', 'bags', 'dailyPrice'];
        $carTypeId = $args['id'];

        $jsonData = json_decode($request->getBody(), true);

        $data = [
            $fieldList[$jsonData['fieldIndex']] => $jsonData['value']
        ];

        $errorList = Validator::carType($jsonData, false);

        if(empty($errorList)){
            DB::update('carTypes',$data,'id=%s',$carTypeId);
        }

        $response->getBody()->write(json_encode([
            'errorList' => $errorList
        ]));

        return $response;
    }
}