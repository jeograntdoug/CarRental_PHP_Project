<?php

require __DIR__ . '/../../vendor/autoload.php';

use Faker\Factory;

DB::$user = 'carrental';
DB::$password = 'QFzyVmD4mYO8Ah8G';  //each localhost has different password
DB::$dbName = 'carrental';
DB::$port = 3333;
DB::$encoding = 'utf8'; // defaults to latin1 if omitted

$faker = Factory::create();
$csvFile = fopen(__DIR__ . "/../../Database/baseForCars.csv","r");

$carBaseList = [];
fgets($csvFile); // ingore first line(title)
while($line = fgets($csvFile)){
    $data = explode(",",$line);

    $carBase = [
        'carTypeId' => $data[0],
        'model' => $data[1],
        'manufacturer' => $data[2],
        'fuelType' => $data[3],
        'photoPath' => $data[4],
        'description' => trim($data[5])
    ];
    array_push($carBaseList, $carBase);
}
fclose($csvFile);

$storeIdList = DB::query('SELECT id, latitude, longitude FROM stores');
$statusList = ['avaliable', 'avaliable', 'avaliable', 'avaliable', 'reserved', 'repair', 'renting'];

$carList = [];
for( $i = 0 ; $i < 200 ; $i++)
{
    $randCarBase = $carBaseList[rand(0, count($carBaseList) - 1)];
    $randYear = rand(1900,date("Y"));
    $randMilleage = rand(21000,100000);
    $randStatus = $statusList[rand(0, count($statusList) - 1)];
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