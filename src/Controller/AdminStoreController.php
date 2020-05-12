<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use DB;

class AdminStoreController
{
    public function index (Request $request, Response $response){
        // TODO : Authentication
        $view = Twig::fromRequest($request);

        $storeList = DB::query("SELECT * FROM stores");

        return $view->render($response, 'admin/cards/store_card.html.twig', [
            'storeList' => $storeList
        ]);
    }

    public function delete (Request $request, Response $response, array $args){
        // TODO : Authentication
        $storeId = $args['id'];

        DB::delete('stores','id=%s',$storeId);

        return $response;
    }

    public function edit (Request $request, Response $response, array $args){
        // TODO : Authentication
        
        $fieldList = ['id','province','city','postCode','address','phone'];
        $storeId = $args['id'];

        $jsonData = json_decode($request->getBody(), true);
        // TODO : validate $post

        $data = [
            $fieldList[$jsonData['fieldIndex']] => $jsonData['value']
        ];

        DB::update('stores',$data,'id=%s',$storeId);

        return $response;
    }
}