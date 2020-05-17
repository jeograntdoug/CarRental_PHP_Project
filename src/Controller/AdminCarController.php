<?php

namespace App\Controller;

use App\Validator;

class AdminCarController extends AdminController
{
    public function __construct(){
        $this->itemTitle = 'Car';

        $this->tableName = 'cars';

        $this->fieldList = [
            'id', 'carTypeId', 'model', 'year', 'manufacturer', 'mileage', 
            'status', 'storeId', 'description', 'fuelType', 'photoPath'
        ];

        $this->fieldListInHeader = [
            'ID', 'CAR TYPE', 'MODEL', 'YEAR', 'MANUFACTURER', 'MILEAGE', 
            'STATUS', 'STORE', 'DESCRIPTION', 'FUEL TYPE'
        ];


        $this->validator = Validator::car();
    }
}