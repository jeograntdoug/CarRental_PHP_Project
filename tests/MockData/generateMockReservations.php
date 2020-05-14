<?php

require __DIR__ . '/../../vendor/autoload.php';

use Faker\Factory;

DB::$user = 'carrental';
DB::$password = 'QFzyVmD4mYO8Ah8G';  //each localhost has different password
DB::$dbName = 'carrental';
DB::$port = 3333;
DB::$encoding = 'utf8'; // defaults to latin1 if omitted

const TEN_YEARS = 60*60*24*365*10;
const A_DAY = 60*60*24;
const AN_HOUR = 60*60;

$faker = Factory::create();

/** 
 *  1.Reservation
 * - choose store location
 * - show only avaliable cars
 * - choose car
 * - Change status of car
 * */ 
  
/**
 *  2.Order
 * - Pick up car ( reservation -> order )
 * - Change status of car
 */

/**
 *  3.Return
 * - Change status of car
 * - set return store/ milleage
 */





$storeIdList = DB::query('SELECT id FROM stores');
$userIdList = DB::query('SELECT id FROM users');
$carTypeIdList = DB::query('SELECT id FROM carTypes');

$carList = [];
for( $i = 0 ; $i < 100 ; $i++)
{
    $randCreatedTS = rand(time() - TEN_YEARS ,time() - 15 * A_DAY);
    $randUserId = $userIdList[rand(0, count($userIdList) - 1)];
    $randCarTypeId = $carTypeIdList[rand(0, count($carTypeIdList) - 1)];

    $timeBegin = $randCreatedTS + rand(AN_HOUR, 15 * A_DAY); 
    $timeEnd = $timeBegin + rand();

    $randStartTime = rand( $timeBegin, $timeEnd );
    $randStartTime = rand(
        $randCreatedTS + rand(0, AN_HOUR), 
        $randCreatedTS + rand(A_DAY/2, 30 * A_DAY) 
    );
    $randStore = $storeIdList[rand(0, count($storeIdList) - 1)];

    $car = [
        'carTypeId' => $randCarBase['carTypeId'],
        'model' => $randCarBase['model'],
        'year' => $randYear,
        'manufacturer' => $randCarBase['manufacturer'],
        'milleage' => $randMilleage,
        'status' => $randStatus,
        'storeId' =>$randStore['id'],
        'description' => $randCarBase['description'],
        'photoPath' => $randCarBase['photoPath'],
        'fuelType' => $randCarBase['fuelType'],
        'latitude' => $randStore['latitude'],
        'longitude' => $randStore['longitude'],
    ];
    array_push($carList,$car);
}


$csvFile = fopen(__DIR__ . "/../../Database/mockCars.csv","w");
$titleLine = 'id,'. implode(",", array_keys($carList[0])). "\n";

fputs($csvFile,$titleLine);
$id = 1;
foreach($carList as $car)
{
    $line = '"' . $id++ . '",' . implode(",", $car). "\n";
    fputs($csvFile, $line);
}
fclose($csvFile);