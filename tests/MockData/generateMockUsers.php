<?php

require __DIR__ . '/../../vendor/autoload.php';

use Faker\Factory;

DB::$user = 'carrental';
DB::$password = 'QFzyVmD4mYO8Ah8G';  //each localhost has different password
DB::$dbName = 'carrental';
DB::$port = 3333;
DB::$encoding = 'utf8'; // defaults to latin1 if omitted


$csvFile = fopen(__DIR__ . "/../../Database/mockUsers.csv","w");
$user = createUser();
$titleLine = 'id,'. implode(",", array_keys($user)). "\n";
// $titleLine =implode(",", array_keys($user)). "\n";

fputs($csvFile,$titleLine);

$id = 1;
for($i = 0 ; $i < 20 ; $i++)
{
    $user = createUser();
    $line = '"' . $id++ . '",' . implode(",", $user). "\n";
    // $line = implode(",", $user). "\n";
    fputs($csvFile, $line);
}
fclose($csvFile);



function createUser($user = null){
    $faker = Factory::create();

    $newUser['firstname'] = isset($user['firstname'])
                    ? $user['firstname'] : $faker->firstName;
    $newUser['lastname'] = isset($user['lastname'])
                    ? $user['lastname'] : $faker->lastName;
    $newUser['drivinglicense'] = isset($user['drivinglicense'])
                    ? $user['drivinglicense'] 
                    : $faker->regexify('[A-Z][0-9]{4}-[0-9]{5}-[0-9]{5}');
    $newUser['address'] = isset($user['address'])
                    ? $user['address'] : $faker->streetAddress;
    $newUser['phone'] = isset($user['phone'])
                    ? $user['phone'] : $faker->regexify('([0-9]{3})[0-9]{3}-[0-9]{4}');
    $newUser['role'] = isset($user['role']) ? $user['role'] : 'user';

    $newUser['email'] = isset($user['email'])
                    ? $user['email'] : $newUser['firstname']. "@gmail.com";
    $newUser['password'] = isset($user['password'])
                    ? $user['password'] : 'q1w2E3';
    return $newUser;
}