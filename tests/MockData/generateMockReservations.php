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
 *  2.Order
 * - Pick up car ( reservation -> order )
 * - Change status of car
 */

/**
 *  3.Return
 * - Change status of car
 * - set return store/ milleage
 */





$storeIdList = DB::queryFirstColumn('SELECT id FROM stores');
$userIdList = DB::queryFirstColumn('SELECT id FROM users');
// $carTypeIdList = DB::query('SELECT id FROM carTypes');

$resvList= [];
for( $i = 0 ; $i < 200 ; $i++)
{
/** 
 *  1.Reservation
 * - choose a user
 * - choose a store location
 * - show only avaliable cars
 * - choose car
 * - Change status of car
 * */ 

    // offset: 45 days

    $randCreatedTS = rand(time() - 5 * AN_YEAR, time());

    $randTimeBegin = $randCreatedTS + rand(AN_HOUR, 30 * A_DAY); 
    $randTimeEnd = $randTimeBegin + rand(A_DAY, 60 * A_DAY);

    $rentDays = ceil(($randTimeEnd - $randTimeBegin) / A_DAY);
    if($rentDays == 0){
        var_dump("hello");
    }

    // TODO : check if user has reservation in this period
    $randUserId = $userIdList[rand(0, count($userIdList) - 1)];
    $randStoreId = $storeIdList[rand(0, count($storeIdList) - 1)];

    $randCreatedTS = date("Y-m-d H:i:s",$randCreatedTS);
    $randTimeBegin = date("Y-m-d H:i:s",$randTimeBegin);
    $randTimeEnd = date("Y-m-d H:i:s",$randTimeEnd);

    // $carTypeIdList = DB::queryFirstColumn(
    //     "SELECT DISTINCT carTypeId FROM cars
    //     WHERE storeId = %s
    //     AND status = 'avaliable'", 
    //     $randStoreId
    // );

    $carTypeIdList = DB::queryFirstColumn(
        "SELECT DISTINCT c.carTypeId 
        FROM cars AS c
        LEFT JOIN reservations AS r
        ON r.carTypeId = c.carTypeId
        WHERE c.storeId = %s
        AND (
            startDateTime IS NULL 
            OR startDateTime > %s
            OR returnDateTime < %s
        )",
        $randStoreId,
        $randTimeEnd,
        $randTimeBegin
    );



    if(empty($carTypeIdList)){
        continue;
    }
    $randCarTypeId = $carTypeIdList[rand(0, count($carTypeIdList) - 1)];


    $dailyPrice = DB::queryFirstField(
        "SELECT dailyPrice 
        FROM carTypes 
        WHERE id =%s", $randCarTypeId );

    $netFees = $rentDays * $dailyPrice;
    $tps = $netFees * 0.10;
    $tvq = $netFees * 0.05;

    $reservation = [
        'createdTS' => $randCreatedTS,
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

    DB::insert("reservations",$reservation);
}