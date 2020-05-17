<?php

namespace App\Controller;

class AdminReservationController extends AdminManuController 
{
    public function __construct(){
        $this->itemTitle = 'Reservation';

        $this->tableName = 'reservations';

        $this->fieldList = [
            'id', 'userId', 'carTypeId', 'startDateTime', 'returnDateTime',
            'dailyPrice', 'netFees', 'rentStoreId', 'returnStoreId'
        ];
    }
}