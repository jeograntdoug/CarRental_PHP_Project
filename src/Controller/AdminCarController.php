<?php

namespace App\Controller;

use App\Validator;

class AdminCarController extends AdminManuController
{
    public function __construct(){
        $this->itemTitle = 'Car';

        $this->tableName = 'cars';

        $this->fieldList = [
            'id', 'carTypeId', 'model', 'year', 'manufacturer', 'mileage', 
            'status', 'storeId', 'description', 'fuelType'
        ];

        $this->validator = Validator::car();
    }
}