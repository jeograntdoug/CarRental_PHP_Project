<?php

use App\Validator;
use PHPUnit\Framework\TestCase;

class StoreValidatorTest extends TestCase
{
    protected $v;

    public static function setUpBeforeClass(): void
    {
        DB::$user = 'carrental';
        DB::$password = 'QFzyVmD4mYO8Ah8G';  //each localhost has different password
        DB::$dbName = 'carrental';
        DB::$port = 3333;
        DB::$encoding = 'utf8'; // defaults to latin1 if omitted
    }

    public static function tearDownAfterClass(): void
    {
    }

    protected function setUp():void
    {
    }

    protected function tearDown():void
    {
    }

    /** @test */
    public function putRequest_emptyFieldIsNotValid(){
        $isPut = true;
        $store = [
            'city' => "",
            'address' => "",
            'phone' => "",
            'postCode' => "",
            'province' => ""
        ];

        $errorList = Validator::store($store,$isPut);
        $this->assertCount(5,$errorList);

        $store = [];

        $errorList = Validator::store($store,$isPut);
        $this->assertCount(5,$errorList);
    }

    /** @test */
    public function patchRequest_validateFields(){
        $isPut = false;
        // city
        $storeList = [
            ['city' => ""],
            ['city' => null],
            ['city' => "lllllllllllllllllllllllllllllllllllllllllllllllllll"],
        ];

        foreach($storeList as $store){
            $errorList = Validator::store($store,$isPut);
            $this->assertCount(1,$errorList);
        }

        // address
        $storeList = [
            ['address' => "41,Rue montreal"],
        ];

        foreach($storeList as $store){
            $errorList = Validator::store($store,$isPut);
            $this->assertEmpty($errorList);
        }

        $storeList = [
            ['address' => ""],
            ['address' => null],
            ['address' => 
                "lllllllllllllllllllllllllllllllllllllllllllllllllll"
                . "lllllllllllllllllllllllllllllllllllllllllllllllllll"],
        ];

        foreach($storeList as $store){
            $errorList = Validator::store($store,$isPut);
            $this->assertCount(1,$errorList);
        }


        // phone
        $storeList = [
            ['phone' => "5145145145"],
            ['phone' => "51454145145"],
            ['phone' => "514-514-5145"],
            ['phone' => "514-4514-5145"],
            ['phone' => "(514)514-5145"],
            ['phone' => "(514)4514-5145"],
            ['phone' => "514-4--514-5145"],
        ];

        foreach($storeList as $store){
            $errorList = Validator::store($store,$isPut);
            $this->assertEmpty($errorList);
        }

        $storeList = [
            ['phone' => ""],
            ['phone' => null],
            ['phone' => "(514)-514-5145"],
            ['phone' => "((51))445145145"],
        ];

        foreach($storeList as $store){
            $errorList = Validator::store($store,$isPut);
            $this->assertCount(1,$errorList);
        }

        // postCode
        $storeList = [
            ['postCode' => "H9X9X9"],
            ['postCode' => "H9X 9X9"],
        ];

        foreach($storeList as $store){
            $errorList = Validator::store($store,$isPut);
            $this->assertEmpty($errorList);
        }

        $storeList = [
            ['postCode' => ""],
            ['postCode' => null],
            ['postCode' => "HH99X9"],
            ['postCode' => "H9HX99"],
            ['postCode' => "H9X  9X9"],
            ['postCode' => "Y2A 9X9"],// There is no Y2A postalcode in DB
            ['postCode' => "Z1A 9X9"],// There is no Z1A postalcode in DB
        ];

        foreach($storeList as $store){
            $errorList = Validator::store($store,$isPut);
            $this->assertCount(1,$errorList);
        }

        // province
        $provinceList 
            = ['NL','PE','NS','NB','QC','ON','MB','SK','AB','BC','YT','NT','NU'];

        foreach($storeList as $store){
            $errorList = Validator::store($provinceList,$isPut);
            $this->assertEmpty($errorList);
        }

        $storeList = [
            ['postCode' => ""],
            ['postCode' => null],
            ['postCode' => "HH"],
            ['postCode' => "AA"],
        ];

        foreach($storeList as $store){
            $errorList = Validator::store($store,$isPut);
            $this->assertCount(1,$errorList);
        }
    }
}