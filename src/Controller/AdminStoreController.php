<?php

namespace App\Controller;

use App\Validator;

class AdminStoreController extends AdminManuController
{
    public function __construct(){
        $this->itemTitle = 'Store';

        $this->tableName = 'stores';

        $this->fieldList = [
            'id', 'storeName', 'province', 'city', 
            'postCode', 'address', 'phone'
        ];

        $this->validator = Validator::store();
    }
}