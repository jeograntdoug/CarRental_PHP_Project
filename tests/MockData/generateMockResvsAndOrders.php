<?php

require __DIR__ . '/../../vendor/autoload.php';

use Faker\Factory;

DB::$user = 'carrental';
DB::$password = 'QFzyVmD4mYO8Ah8G';  //each localhost has different password
DB::$dbName = 'carrental';
DB::$port = 3333;
DB::$encoding = 'utf8'; // defaults to latin1 if omitted

const AN_YEAR = 60*60*24*365;
const A_DAY = 60*60*24;
const AN_HOUR = 60*60;

$faker = Factory::create();

/**
 * Start Generating fields in orders table
 */
$carList = DB::query("SELECT id, year FROM cars");

foreach($carList as $car){
    $baseMileage = 0;
    for($i = 0 ; $i < date("Y") - $car['year'] ; $i++){
        $baseMileage += rand(2000,10000);
    }  
    DB::update('cars',[ 'mileage' => $baseMileage], "id=%s", $car['id']);
}


$storeIdList = DB::queryFirstColumn('SELECT id FROM stores');
$userIdList = DB::queryFirstColumn('SELECT id FROM users');
// $carTypeIdList = DB::query('SELECT id FROM carTypes');

$resvList= [];
for( $i = 0 ; $i < 2000 ; $i++)
{

    /**
     * Start Generating fields in orders table
     */
    $randCreatedTS = rand(time() - 5 * AN_YEAR, time());

    $randReturnTime = $randCreatedTS + rand(A_DAY, 60 * A_DAY);

    $rentDays = ceil(($randReturnTime - $randCreatedTS) / A_DAY);

    // TODO : check if user has reservation in this period
    $randUserId = $userIdList[rand(0, count($userIdList) - 1)];
    $randStoreId = $storeIdList[rand(0, count($storeIdList) - 1)];

    $randCreatedTS = date("Y-m-d H:i:s", $randCreatedTS);
    $randReturnTime = date("Y-m-d H:i:s", $randReturnTime);

    $carList = DB::query(
        "SELECT c.id AS 'id', c.mileage AS 'mileage',
            c.carTypeId AS 'carTypeId'
        FROM cars AS c
        LEFT JOIN orders AS o
        ON o.carId = c.id
        WHERE c.storeId = %s
        AND c.year < %s
        AND (
            createdTS IS NULL 
            OR createdTS > %s
            OR returnDateTime < %s
        )",
        $randStoreId,
        date('Y',strtotime($randCreatedTS)),
        $randReturnTime,
        $randCreatedTS
    );



    if(empty($carList)){
        continue;
    }

    $randCar = $carList[rand(0, count($carList) - 1)];


    $dailyPrice = DB::queryFirstField(
        "SELECT dailyPrice 
        FROM carTypes 
        WHERE id =%s", $randCar['carTypeId']);

    $totalPrice = $rentDays * $dailyPrice * 1.15;

    $randReturnMileage = $randCar['mileage'] + rand(20,300) * $rentDays;

    /**
     * End of Generating fields in orders table
     */


    /**
     * Start Generating fields in reservations table
     */

    $randTimeBegin = $randCreatedTS;
    $randTimeEnd = $randReturnTime;
    $randResvCreatedTS = strtotime($randCreatedTS) - rand(AN_HOUR, 30 * A_DAY); 

    $randResvCreatedTS = date("Y-m-d H:i:s",$randResvCreatedTS);

    $randCarTypeId = $randCar['carTypeId'];

    $netFees = $rentDays * $dailyPrice;
    $tps = $netFees * 0.10;
    $tvq = $netFees * 0.05;

    /**
     * End of Generating fields in reservations table
     */

    $reservation = [
        'createdTS' => $randResvCreatedTS,
        'userId' => $randUserId,
        'carTypeId' => $randCarTypeId,
        'startDateTime' => $randTimeBegin,
        'returnDateTime' => $randTimeEnd,
        'dailyPrice' => $dailyPrice,
        'netFees' => $netFees,
        'tps' => $tps,
        'tvq' => $tvq,
        'rentDays' => $rentDays,
        'rentStoreId' => $randStoreId,
        'returnStoreId' => $randStoreId,
    ];

    DB::insert("reservations", $reservation); 
    $resvId = DB::insertId();


    $order = [
        'createdTS' => $randCreatedTS,
        'reservationId' => $resvId,
        'userId' => $randUserId,
        'carId' => $randCar['id'],
        'returnDateTime' => $randReturnTime,
        'startMileage' => $randCar['mileage'],
        'returnMileage' => $randReturnMileage,
        'totalPrice' => $totalPrice,
        'rentStoreId' => $randStoreId,
        'returnStoreId' => $randStoreId,
    ];

    DB::insert("orders", $order);
    $orderId = DB::insertId();
    DB::update("cars", ['mileage' => $randReturnMileage], "id=%s", $randCar['id']);
}

