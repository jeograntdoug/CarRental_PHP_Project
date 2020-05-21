<?php

namespace App\Controller;

use App\Validator;

class AdminStoreController extends AdminController
{
    public function __construct(){
        $this->itemTitle = 'Store';

        $this->tableName = 'stores';

        $this->fieldList = [
            'id', 'storeName', 'province', 'city', 
            'postCode', 'address', 'phone'
        ];

        $this->fieldListInHeader = [
            'ID', 'NAME', 'PROVINCE', 'CITY', 
            'POST CODE', 'ADDRESS', 'PHONE'
        ];

        $this->validator = Validator::store();
    }
}