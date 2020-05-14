<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use DB;

class AdminController
{
    public function home(Request $request, Response $response){
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/index.html.twig');
    }


    public function storeList(Request $request, Response $response){
        $view = Twig::fromRequest($request);

        $storeList = DB::query(
            "SELECT id, storeName, province, city, postCode, address, phone
             FROM stores"
             );

        return $view->render($response, 'admin/store_list.html.twig',[
            'itemList' => $storeList,
            'itemKeyList' => ['ID', 'Store Name', 'Province', 'City', 'Post Code', 'Address', 'Phone']
        ]);
    }



    public function carList(Request $request, Response $response){
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/car_list.html.twig');
    }


    public function reservationList(Request $request, Response $response){
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/reservation_list.html.twig');
    }
    
}