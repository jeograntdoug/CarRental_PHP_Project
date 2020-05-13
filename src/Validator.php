<?php

namespace App; 
use Respect\Validation\Validator as v;
use DB;


class Validator
{
    public static function store($store, $isPut = true)
    {
        $errorList = [];
        if (!v::notEmpty()->validate($store)) {
            $errorList = [
                'city' => 'Must be 1~20 letters',
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
    }
}