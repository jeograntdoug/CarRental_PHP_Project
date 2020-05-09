<?php

use PHPUnit\Framework\TestCase;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

require('vendor/autoload.php');

class UserControllerTest extends TestCase
{
    protected $client;

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        DB::$user = 'carrental';
        DB::$password = 'QFzyVmD4mYO8Ah8G';
        DB::$dbName = 'carrental';
        DB::$port = 3333;
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
            'http_errors' => false
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
            'drivinglicense' => 'D1234-12345-12345',
            'address' => '12-1, rue Montreal',
            'phone' => '(555)555-5555',
            'role' => 'user',
            'email' => 'johndoe@example.com',
            'password' => 'q1w2E3',
            'confirm' => 'q1w2E3'
        ];

        $response = $this->client->post('/register', [
            'form_params' =>$johnDoe
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $countJohnDoe = DB::queryFirstField("SELECT COUNT(*) FROM users WHERE email=%s",$johnDoe['email']);

        $this->assertEquals(1, $countJohnDoe);
    }

    private function createMultipart($user, $photoPath){
        $multipart = [];

        foreach($user as $key => $value){
            array_push(
                $multipart, 
                [
                    'name' => $key,
                    'contents' => $value,
                ]
            );
        }
        
        array_push(
            $multipart,
            [
                'name' => 'idPhoto',
                'contents' => fopen($photoPath,'r')
            ]
        );
        return $multipart;
    }

    /**
     * @test
     */
    public function guestCanRegisterWithPhoto() {
        $janeDoe = [
            'firstname' => 'jane',
            'lastname' => 'doe',
            'drivinglicense' => 'D1234-12345-12345',
            'address' => '12-1, rue Montreal',
            'phone' => '(555)555-5555',
            'role' => 'user',
            'email' => 'janedoe@example.com',
            'password' => 'q1w2E3',
            'confirm' => 'q1w2E3'
        ];

        $multipart = $this->createMultipart($janeDoe,__DIR__. '/../tmp/test.jpg');
        $response = $this->client->post('/register', [
            'multipart' => $multipart
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $countJaneDoe = DB::queryFirstField("SELECT COUNT(*) FROM users WHERE email=%s",$janeDoe['email']);

        $this->assertEquals(1, $countJaneDoe);
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

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     *  @depends guestCanRegister 
     *  @test 
    */
    public function nonRegisterUserCannotLogin(){
        $response = $this->client->post('/login',[
            'form_params' => [
                'email' => 'janeDoe@example.com',
                'password' => 'q1w2E#'
            ]
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }
}