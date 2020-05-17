<?php

namespace App\Controller;

class AdminCarTypeController extends AdminManuController
{
    public function __construct(){
        $this->itemTitle = 'CarType';

        $this->tableName = 'carTypes';

        $this->fieldList = [
            'id', 'category', 'subtype', 'description', 
            'passengers', 'bags', 'dailyPrice'
        ];

        $this->validator = "Validator::carType";
    }
}