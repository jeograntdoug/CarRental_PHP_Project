<?php

namespace App\Controller;

use App\Validator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use DB;

class AdminOrderController extends AdminManuController
{
    public function __construct(){
        $this->itemTitle = 'Order';

        $this->tableName = 'orders';

        $this->fieldList = [
            'id', 'reservationId', 'userId', 'carId', 'createdTS', 
            'returnDateTime', 'totalPrice', 'rentStoreId', 'returnStoreId',
        ];
    }
}