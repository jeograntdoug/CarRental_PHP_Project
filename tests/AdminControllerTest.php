<?php

use PHPUnit\Framework\TestCase;

class AdminControllerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        DB::$user = 'carrental';
        DB::$password = 'QFzyVmD4mYO8Ah8G';
        DB::$dbName = 'carrental';
        DB::$port = 3333;

    }

    public static function tearDownAfterClass(): void
    {
        DB::query('DELETE FROM users');
    }

    protected function setUp():void
    {

        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'http://project.ipd20:8888',
            'http_errors' => false
        ]);


    }

    protected function tearDown():void
    {
    }

    /** @test */
    public function onlyAdminCanAccessAdminPage(){
        $response = $this->client->post('/login',[
            'form_params' => [
                'email' => 'johndoe@example.com',
                'password' => 'q1w2E3'
            ]
        ]);

        // $this->assertEquals(200, $response->getStatusCode());
    }
}