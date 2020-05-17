<?php

namespace App\Controller;

class AdminCarTypeController extends AdminController
{
    public function __construct(){
        $this->itemTitle = 'CarType';

        $this->tableName = 'carTypes';

        $this->fieldList = [
            'id', 'category', 'subtype', 'description', 
            'passengers', 'bags', 'dailyPrice', 'photoPath'
        ];

        $this->fieldListInHeader = [
            'ID', 'CATEGORY', 'TYPE', 'DESCRIPTION', 
            'PASSENGERS', 'BAGS', 'DALIY PRICE'
        ];
;

        $this->validator = "Validator::carType";
    }
}