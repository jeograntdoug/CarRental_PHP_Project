<?php

use PHPUnit\Framework\TestCase;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

require('vendor/autoload.php');

class UserControllerTest extends TestCase
{
    protected $client;
    protected $faker;

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        DB::$user = 'carrental';
        DB::$password = 'sRPJwMOei4Y8lquD';
        DB::$dbName = 'carrental';
        DB::$port = 3333;

        $this->faker = Faker\Factory::create();
    }

    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass(): void
    {
        DB::query("DELETE FROM users");
    }

    protected function setUp():void
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'http://project.ipd20:8888',
            // 'http_errors' => false
        ]);

    }

    protected function tearDown(): void
    {
    }


    /**
     * @test
     */
    public function guestCanRegister() {
        $johnDoe = [
            'firstname' => 'john',
            'lastname' => 'doe',
            'drivinglicense' => '123456789',
            'address' => '123, rue johnabbott',
            'phone' => '123-456-789',
            'role' => 'user',
            'email' => 'johndoe@example.com',
            'password' => 'q1w2E#'
        ];

        $response = $this->client->post('/user/create', [
            'form_params' =>$johnDoe
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $countJohnDoe = DB::queryFirstField("SELECT COUNT(*) FROM users WHERE email=%s",$johnDoe['email']);

        $this->assertEquals(1, $countJohnDoe);
    }

    /**
     *  @depends guestCanRegister 
     *  @test 
    */
    public function userCanLogin(){
        $response = $this->client->post('/login',[
            'form_params' => [
                'email' => 'johndoe@example.com',
                'password' => 'q1w2E#'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

    }
}