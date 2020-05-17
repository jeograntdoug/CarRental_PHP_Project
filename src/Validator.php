<?php

namespace App; 
use Respect\Validation\Validator as v;
use DB;


class Validator
{
    public static function store()
    {
        return function ($store, $isPut = true) {
            $errorList = [];
            if (!v::notEmpty()->validate($store)) {
                $errorList = [
                    'city' => 'Must be 1~20 letters',
                    'storeName' => 'Must be 1~50 letters',
                    'address' => 'Must be 1~100 letters',
                    'phone' => 'Invalid Phone number',
                    'postCode' => 'Invalid Postal Code',
                    'province' => 'Invalid Province',
                ];
                return $errorList;
            }


            // city
            if (!v::key('city', v::stringVal()->length(1, 50), $isPut)->validate($store)) {
                $errorList['city'] = 'Must be 1~50 letters';
            }

            // storeName
            if (!v::key('storeName', v::stringVal()->length(1, 50), $isPut)->validate($store)) {
                $errorList['storeName'] = 'Must be 1~50 letters';
            }

            // address
            if (!v::key('address', v::stringVal()->length(1, 100), $isPut)->validate($store)) {
                $errorList['address'] = 'Must be 1~100 letters';
            }

            // phone
            if (!v::key('phone', v::phone(), $isPut)->validate($store)) {
                $errorList['phone'] = 'Invalid Phone number';
            }

            // Get all data in uppercase from client without white space
            // postal code
            if (v::key('postCode', v::regex('/^[a-zA-Z][0-9][a-zA-Z] ?[0-9][a-zA-Z][0-9]$/'),$isPut)->validate($store)) 
            {
                if(isset($store['postCode'])){
                    $firstThreeCode = substr($store['postCode'],0,3);
                    if(DB::queryFirstRow(
                        "SELECT * FROM cities
                        WHERE postalCode=%s",$firstThreeCode) == null)
                    {
                        $errorList['postCode'] = "Postal Code doesn't exist in Canada";
                    }
                }
            } else{
                $errorList['postCode'] = 'Must be Postal Code in Canada';
            }

            // Get all data in uppercase from client
            // province
            $provinceList 
                = ['NL','PE','NS','NB','QC','ON','MB','SK','AB','BC','YT','NT','NU',
                'nl','pe','ns','nb','qc','on','mb','sk','ab','bc','yt','nt','nu'];
            if (!v::key('province', v::in($provinceList), $isPut)->validate($store)) {
                $errorList['province'] = 'Must be Province Code in Canada';
            }

            return $errorList;
        };
    }


    public static function carType()
    {
        return function ($carType, $isPut = true)
        {
            $errorList = [];
            if (!v::notEmpty()->validate($carType)) {
                $errorList = [
                    'category' => 'Must be 1~20 letters',
                    'subtype' => 'Must be 1~20 letters',
                    'description' => 'Must be 1~500 letters',
                    'passengers' => 'Must be 1~100 number',
                    'bags' => 'Must be 1~100 number',
                    'dailyPrice' => 'Must be positive number',
                ];
                return $errorList;
            }


            // category
            if (!v::key('category', v::stringVal()->length(1, 20), $isPut)->validate($carType)) {
                $errorList['category'] = 'Must be 1~20 letters';
            }

            // subtype
            if (!v::key('subtype', v::stringVal()->length(1, 20), $isPut)->validate($carType)) {
                $errorList['subtype'] = 'Must be 1~20 letters';
            }

            // description
            if (!v::key('description', v::stringVal()->length(1, 500), $isPut)->validate($carType)) {
                $errorList['description'] = 'Must be 1~500 letters';
            }

            // passengers
            if (!v::key('passengers', v::exists()->between(1,100), $isPut)->validate($carType)) {
                $errorList['passengers'] = 'Must be 1~100 number';
            }

            // bags
            if (!v::key('bags', v::number()->between(1,100), $isPut)->validate($carType)) {
                $errorList['bags'] = 'Must be 1~100 number';
            }

            // dailyPrice
            if (!v::key('dailyPrice', v::positive()->floatVal(), false)->validate($carType)) {
                $errorList['dailyPrice'] = 'Must be positive number';
            }


            return $errorList;
        };
    }

    public static function car()
    {
        return function ($car, $isPut = true) 
        {
            $statusList = ['avaliable', 'reserved', 'repair', 'renting'];
            $fuelTypeList = ['gas', 'diesel', 'dybrid'];
            $currentYear = date("Y");
            $errorList = [];

            if (!v::notEmpty()->validate($car)) {
                $errorList = [
                    'carTypeId' => 'Invalid Car Type Id',
                    'model' => 'Must be 1~20 letters',
                    'year' => 'Must be 1900 ~ ' . $currentYear,
                    'manufacturer' => 'Must be 1~20 letters',
                    'milleage' => 'Must be positive number',
                    'status' => 'Must be ['. implode(',' ,$statusList) . ']',
                    'dailyPrice' => 'Must be positive number',
                    'storeId' => 'Invalid store id', 
                    'description' => 'Must be 1~500 letters',
                    'fuelType' => 'Must be ['. implode(',' ,$fuelTypeList) . ']'
                ];

                return $errorList;
            }


            // carTypeId
            $carTypeIdList = DB::queryFirstColumn("SELECT id FROM carTypes");
            if (!v::key('carTypeId', v::in($carTypeIdList), $isPut)->validate($car)) {
                $errorList['carTypeId'] = 'Invalid Car Type Id';
            }

            // model
            if (!v::key('model', v::stringVal()->length(1, 20), $isPut)->validate($car)) {
                $errorList['model'] = 'Must be 1~20 letters';
            }

            // year
            if (!v::key('year', v::intVal()->between(1900, $currentYear), $isPut)->validate($car)) {
                $errorList['year'] = 'Must be 1900 ~ ' . $currentYear;
            }
                    
            // manufacturer
            if (!v::key('manufacturer', v::stringVal()->length(1,20), $isPut)->validate($car)) {
                $errorList['manufacturer'] = 'Must be 1~20 letters';
            }

            // milleage
            if (!v::key('milleage', v::intVal()->positive(), $isPut)->validate($car)) {
                $errorList['milleage'] = 'Must be positive number';
            }

            // status
            if (!v::key('status', v::in($statusList), false)->validate($car)) {
                $errorList['status'] = 'Must be ['. implode(',' ,$statusList) . ']';
            }
            
            // dailyPrice
            if (!v::key('dailyPrice', v::floatVal()->positive(), false)->validate($car)) {
                $errorList['dailyPrice'] = 'Must be positive number';
            }
            
            // storeId
            $storeIdList = DB::queryFirstColumn("SELECT id FROM stores");
            if (!v::key('storeId', v::in($storeIdList), false)->validate($car)) {
                $errorList['storeId'] = 'Invalid store id';
            }

            // description
            if (!v::key('description', v::stringVal()->length(1, 500), false)->validate($car)) {
                $errorList['description'] = 'Must be 1~500 letters';
            }

            // fuelType
            if (!v::key('fuelType', v::in($fuelTypeList), false)->validate($car)) {
                $errorList['fuelType'] = 'Must be ['. implode(',' ,$fuelTypeList) . ']';
            }
        
            return $errorList;
        };
    }

    //TODO : reservation validation
    //TODO : order validation


}