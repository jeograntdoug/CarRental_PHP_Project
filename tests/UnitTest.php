<?php

use PHPUnit\Framework\TestCase;

use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class UnitTest extends TestCase
{
    // protected $app;

    // protected function setup() : void
    // {
    //     $this->app = (require __DIR__ . '/../config/bootstrap.php');
    // }

    // // NOT WORKING...
    // public function testGetRequestReturnsEcho()
    // {
    //     $requestFactory = new ServerRequestFactory();

    //     $request = $requestFactory->createServerRequest('GET','/errors/forbidden');

    //     $response = $this->app->handle($request);

    //     $data = json_decode($response->getBody(), true);

    //     var_dump($data);
    // }
}